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

    /**
     * Control the user input, if available.
     *
     * @return boolean True if all user input was accepted.
     */
    public function controlInput(): bool
    {
        $this->initializeMpd();

        $serviceHandler = $GLOBALS['pluginRegistration']->getPlugin('Service', 'Local', 'Mpd');

        $this->mpdState = $serviceHandler->getState($this->mpdService);
        $this->mpdCurrentSong = $serviceHandler->getCurrentSong($this->mpdService);
        $this->currentCover = $this->getCurrentCover($this->mpdCurrentSong, $serviceHandler);

        return true;
    }

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {
        $this->application->getTemplater()->setVar('mpdState', $this->mpdState);
        $this->application->getTemplater()->setVar('mpdCurrentSong', $this->mpdCurrentSong);
        $this->application->getTemplater()->setVar('currentCover', $this->currentCover);

        return parent::controlView();
    }

    public function getCurrentCover(array $currentSong, $serviceHandler)
    {
        $coverHash = md5('coverArt_' . dirname($currentSong['file']));

        $filename = $this->findCoverFile($coverHash);

        if (!$filename) {
            $coverData = $serviceHandler->getCoverFromFile($this->mpdService, $currentSong['file']);

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
                return false;
            }
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

    protected function initializeMpd()
    {
        // Load Backend Plugin Registration
        $pluginRegistration = new \Rabbaz\Core\Plugins\Registration();
        $pluginRegistration->initPlugins();

        $GLOBALS['pluginRegistration'] = $pluginRegistration;

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
