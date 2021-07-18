<?php

namespace Rabbaz\Core\App\Controller\Screen;

class DisplayOutput extends \ForwardFW\Controller\Screen
{
    /** @var array */
    private $mpdState = [];

    /** @var array */
    private $mpdCurrentSong = [];

    /** @var \Rabbaz\Core\Model\Service */
    private $mpdService = null;

    /** @var string */
    private $currentCover = null;

    /** @var \Rabbaz\Core\Plugins\Local\Service\Mpd */
    private $serviceHandler = null;

    /** @var int */
    private $screenId = 0;

    /** @var string */
    private $action = '';

    /**
     * Control the user input, if available.
     *
     * @return boolean True if all user input was accepted.
     */
    public function controlInput(): bool
    {
        $this->initializeMpd();
        $this->screenId = (int) $this->getParameter('id');
        $this->action = $this->getParameter('action');

        $this->serviceHandler = $GLOBALS['pluginRegistration']->getPlugin('Service', 'Local', 'Mpd');

        switch ($this->action) {
            case 'play':
                $this->startPlay();
                break;
            case 'stop':
                $this->stopPlay();
                break;
            default:
                // Nothing to do
        }

        $this->mpdState = $this->serviceHandler->getState($this->mpdService);
        $this->mpdCurrentSong = $this->serviceHandler->getCurrentSong($this->mpdService);
        if (!empty($this->mpdCurrentSong)) {
            $this->currentCover = $this->getCurrentCover(
                dirname($this->mpdCurrentSong['file']),
                $this->mpdCurrentSong['file'],
                'Assets/Images/devices/media-optical.svg'
            );
        }

        return true;
    }

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {

        switch ($this->screenId) {
            case 1:
                $this->application->getTemplater()->setVar(
                    'collection', $this->retrieveRadios()
                );
                break;
            case 2:
                break;
            case 3:
                $this->application->getTemplater()->setVar(
                    'collection', $this->retrieveCollection()
                );
                break;
            default:
                break;
        }

        $this->application->getTemplater()->setVar('mpdState', $this->mpdState);
        $this->application->getTemplater()->setVar('mpdCurrentSong', $this->mpdCurrentSong);
        $this->application->getTemplater()->setVar('currentCover', $this->currentCover);
        $this->application->getTemplater()->setVar('screenId', $this->screenId);

        return parent::controlView();
    }

    public function getCurrentCover(string $pathAlbum, string $pathOneSong, string $default): string
    {
        $coverHash = md5('coverArt_' . $pathAlbum); //$currentSong['file']));
        $filename = $this->findCoverFile($coverHash);

        if (!$filename) {
            $filename = $this->retrieveCover($pathOneSong, $coverHash);
            if (!$filename) {
                /** @TODO save if we do not get any cover to not try receive it every time */
                return $default;
            }
        }

        return $filename;
    }

    public function retrieveCover(string $pathSong, string $coverHash): string
    {
        $coverData = $this->serviceHandler->getCoverFromFile($this->mpdService, $pathSong);

        if (isset($coverData['binaryData']) && $coverData['binaryData']) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($coverData['binaryData']);

            $filename = 'Covers/' . $coverHash;
            switch ($mime) {
                case 'image/jpeg':
                    $filename .= '.jpg';
                    break;
                case 'image/png':
                    $filename .= '.png';
                    break;
                case 'image/gif':
                    $filename .= '.gif';
                    break;
                default:
                    // Not supported format like bmp which should be converted before saving
                    break;
            }

            file_put_contents($filename, $coverData['binaryData']);
        } else {
            return '';
        }

        return $filename;
    }

    public function findCoverFile($coverHash)
    {
        $files = glob('Covers/' . $coverHash . '.*');

        if (empty($files)) {
            return false;
        }

        return $files[0];
    }


    public function retrieveRadios(): array
    {
        $collection = [];
        $collectionPaths = $this->serviceHandler->getPath($this->mpdService, 'Radio');

        foreach ($collectionPaths as $pathAlbum) {
            if (isset($pathAlbum['playlist'])) {
                $playlistEntries = $this->serviceHandler->getPlaylist($this->mpdService, $pathAlbum['playlist']);
                if (isset($playlistEntries[0])) {
                    foreach ($playlistEntries as $playlistEntrie) {
                        if (isset($playlistEntrie['file'])) {
                            // Getting album information from first title
                            array_push(
                                $collection,
                                [
                                    'path' => $playlistEntrie['file'],
                                    'cover' => 'Assets/Images/devices/audio-radio.svg',
                                    'title' => $playlistEntrie['Title'],
                                    'artist' => (isset($playlistEntrie['Artist'])) ? $playlistEntrie['Artist'] : '',
                                ]
                            );
                        }
                    }
                }
            }
        }

        return $collection;
    }

    public function retrieveCollection(): array
    {
        $collection = [];
        $collectionPaths = $this->serviceHandler->getPath($this->mpdService, 'CD');

        foreach ($collectionPaths as $pathAlbum) {
            if (isset($pathAlbum['directory'])) {
                $musicFiles = $this->serviceHandler->getPath($this->mpdService, $pathAlbum['directory']);
                if (isset($musicFiles[0]['file'])) {
                    // Getting album information from first title
                    array_push(
                        $collection,
                        [
                            'path' => $pathAlbum['directory'],
                            'cover' => $this->getCurrentCover($pathAlbum['directory'], $musicFiles[0]['file'], 'Assets/Images/devices/media-optical.svg'),
                            'title' => $musicFiles[0]['Album'],
                            'artist' => $musicFiles[0]['Artist'],
                        ]
                    );
                }
            }
        }

        return $collection;
    }

    protected function startPlay()
    {
        if ($what = $this->getParameter('what')) {
            $this->serviceHandler->startPlay($this->mpdService, $what);
        } else {
            $this->serviceHandler->startPlay($this->mpdService);
        }
    }

    protected function stopPlay()
    {
        $this->serviceHandler->stopPlay($this->mpdService);
    }

    protected function initializeMpd()
    {
        // Load Backend Plugin Registration
        $pluginRegistration = new \Rabbaz\Core\Plugins\Registration();
        $pluginRegistration->initPlugins();

        $GLOBALS['pluginRegistration'] = $pluginRegistration;
        setlocale(LC_CTYPE, "en_US.utf8");

        // Do scanning
        /** @TODO Better API */
        $allScanner = $GLOBALS['pluginRegistration']->getPlugins('Scanner');
        $scannerClass = $allScanner['Local'];
        $scanner = new $scannerClass();
        $this->localEquipment = $scanner->scan()[0];

        // Search the MPD Service
        foreach($this->localEquipment->getServices() as $service) {
            if ($service->getIdentifier() === 'Local:MPD') {
                $this->mpdService = $service;
            }
        }
    }
}
