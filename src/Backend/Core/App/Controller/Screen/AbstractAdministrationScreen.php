<?php

namespace Rabbaz\Core\App\Controller\Screen;

abstract class AbstractAdministrationScreen extends \ForwardFW\Controller\Screen
{

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {
        // Header
        $view = $this->loadView(\Rabbaz\Core\App\Controller\View\NavigationHeader::class);
        $this->addView($view);

        // Sidebar menu View
        $view = $this->loadView(\Rabbaz\Core\App\Controller\View\NavigationSidebar::class);

        $classPath = explode('\\', get_class($this));
        $view->setSelectedScreenName(lcfirst(array_pop($classPath)));
        $this->addView($view);

        return true;
    }
}
