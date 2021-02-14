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

        // Remove path parameters which start with a question mark
        if (($positionParameter = strpos($processScreen, '?')) !== false) {
            $processScreen = substr($processScreen, 0, $positionParameter);
        }

        if (!isset($this->configuredScreens[$processScreen])) {
            $processScreen = array_keys($this->configuredScreens)[0];
        }
        return $processScreen;
    }
}
