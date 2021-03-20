<?php

namespace Rabbaz\Core\App\Controller\Screen;

class HardwareSearch extends AbstractAdministrationScreen
{
    /** @var array Equipments found */
    protected $equipments = [];

    /**
     * Control the user input, if available.
     *
     * @return boolean True if all user input was accepted.
     */
    public function controlInput(): bool
    {
        if ($this->getParameter('search')) {
            $this->searchHardware();
        }
        return true;
    }

    /**
     * Loads Data for views and defines which views to use.
     */
    public function controlView(): bool
    {
        $noEquipment = true;

        if (count($this->equipments) > 0) {
            $view = $this->loadView(\Rabbaz\Core\App\Controller\View\FoundEquipment::class);
            $view->setFoundEquipment($this->equipments);
            $this->addView($view);

            $noEquipment = false;
        }

        $this->application->getTemplater()->setVar('noEquipment', $noEquipment);

        return parent::controlView();
    }

    protected function searchHardware()
    {
        // Load Backend Plugin Registration
        $pluginRegistration = new \Rabbaz\Core\Plugins\Registration();
        $pluginRegistration->initPlugins();

        $GLOBALS['pluginRegistration'] = $pluginRegistration;




        // Do scanning
        $allScanner = $GLOBALS['pluginRegistration']->getPlugins('Scanner');

        foreach ($allScanner as $name => $scannerClass) {
            $scanner = new $scannerClass();
            $foundEquipments = $scanner->scan();

            $this->equipments[$scannerClass]['equipment'] = $foundEquipments;
            $this->equipments[$scannerClass]['errors'] = $scanner->getErrors();
        }

//         echo $this->showEquipments($equipments);

    }
}
