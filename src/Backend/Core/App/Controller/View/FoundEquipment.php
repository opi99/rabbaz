<?php

namespace Rabbaz\Core\App\Controller\View;

class FoundEquipment extends \ForwardFW\Controller\View
{
    /** @var array */
    protected $foundEquipment = [];

    public function setFoundEquipment(array $foundEquipment): void
    {
        $this->foundEquipment = $foundEquipment;
    }

    /**
     * Processes the View.
     */
    public function processView(): string
    {
        $templater = $this->application->getTemplater();
        $templater->setVar('foundEquipment', $this->foundEquipment);

        $function = new \Twig\TwigFunction('ThemedImage', function ($iconName) {
            return \Rabbaz\Core\App\Controller\View\FoundEquipment::showIcon($iconName);
        });
        $templater->getTwigEnvironment()->addFunction($function);

        return parent::processView();
    }

    /** @TODO Move to a general place */
    public static function showIcon(string $iconName): string
    {
        if (strpos($iconName, '://') !== false) {
            // We should download them in a cache and use identifier for them
            return '<img src="' . $iconName . '" width="32px" />';
        } else {
            // https://specifications.freedesktop.org/icon-naming-spec/latest/
            // https://specifications.freedesktop.org/icon-theme-spec/icon-theme-spec/latest/
            return '<img src="' . self::getIconWebPath($iconName) . '" width="32px" />';
        }

        return '';
    }

    public static function getIconWebPath(string $iconName): string
    {
        $themePath = 'Assets/Images/';
        foreach(['.svg', '.png', '.gif'] as $extension) {
            if (is_file($themePath . $iconName . $extension)) {
                return $themePath . $iconName . $extension;
            }
        }

        return $iconName;
    }
}
