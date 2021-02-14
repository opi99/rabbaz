<?php

namespace Rabbaz\Core\App\Controller;

class Application extends \ForwardFW\Controller\Application
{
    /**
     * Returns name of screen to be processed
     *
     * @return string name of screen to process
     */
    public function getProcessScreen(): string
    {
        $processScreen = trim($this->request->getRoutePath(), '/');

        if (!isset($this->configuredScreens[$processScreen])) {
            $processScreen = array_keys($this->configuredScreens)[0];
        }
        return $processScreen;
    }
}
