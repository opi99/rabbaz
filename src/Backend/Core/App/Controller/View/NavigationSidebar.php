<?php

namespace Rabbaz\Core\App\Controller\View;

class NavigationSidebar extends \ForwardFW\Controller\View
{
    /** @var string Name of the screen which is selected */
    protected $selectedScreenName = '';

    public function setSelectedScreenName(string $selectedScreenName): void
    {
        $this->selectedScreenName = $selectedScreenName;
    }

    /**
     * Processes the View.
     */
    public function processView(): string
    {
        $templater = $this->application->getTemplater();
        $templater->setVar('selectedScreenName', $this->selectedScreenName);

        return parent::processView();
    }
}
