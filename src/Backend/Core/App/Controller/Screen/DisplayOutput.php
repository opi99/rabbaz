<?php

namespace Rabbaz\Core\App\Controller\Screen;

class DisplayOutput extends AbstractAdministrationScreen
{

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

        return true;
    }

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {
        $this->application->getTemplater()->setVar('mpdState', $this->mpdState);

        return parent::controlView();
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
