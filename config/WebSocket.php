<?php

declare(strict_types=1);

$templaterConfig = (new ForwardFW\Config\Templater\Twig())
    ->setCompilePath(__DIR__ . '/../cache/')
    ->setTemplatePath(__DIR__ . '/../src/Frontend/Templates');

return (new Rabbaz\Server\Config\Server())
    ->addService(
        (new \ForwardFW\Config\Service\EventDispatcher())
    )
    ->setHost('localhost')
    ->setPort(12345);
