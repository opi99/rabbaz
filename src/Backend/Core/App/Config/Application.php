<?php

namespace Rabbaz\Core\App\Config;

class Application extends \ForwardFW\Config\Application
{
    /** @var string $executionClassName */
    protected $executionClassName = \Rabbaz\Core\App\Controller\Application::class;
}
