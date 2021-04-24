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

    /** @var \Rabbaz\Core\Plugins\Local\Service\Mpd */
    private $serviceHandler = null;

    private $screenId = 0;

    /**
     * Control the user input, if available.
     *
     * @return boolean True if all user input was accepted.
     */
    public function controlInput(): bool
    {
        $this->initializeMpd();
        $this->screenId = (int) $this->getParameter('id');

        $this->serviceHandler = $GLOBALS['pluginRegistration']->getPlugin('Service', 'Local', 'Mpd');

        $this->mpdState = $this->serviceHandler->getState($this->mpdService);
        $this->mpdCurrentSong = $this->serviceHandler->getCurrentSong($this->mpdService);
        $this->currentCover = $this->getCurrentCover(dirname($this->mpdCurrentSong['file']), $this->mpdCurrentSong['file']);

        return true;
    }

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {

        switch ($this->screenId) {
            case 1:
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

        return parent::controlView();
    }

    public function getCurrentCover(string $pathAlbum, string $pathOneSong): string
    {
        $coverHash = md5('coverArt_' . $pathAlbum); //$currentSong['file']));
        $filename = $this->findCoverFile($coverHash);

        if (!$filename) {
            $filename = $this->retrieveCover($pathOneSong, $coverHash);
            if (!$filename) {
                /** @TODO save if we do not get any cover to not try receive it every time */
                return 'Assets/Images/devices/media-optical.svg';
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
                            'cover' => $this->getCurrentCover($pathAlbum['directory'], $musicFiles[0]['file']),
                            'title' => $musicFiles[0]['Album'],
                            'artist' => $musicFiles[0]['Artist'],
                        ]
                    );
                }
            }
        }

        return $collection;
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
