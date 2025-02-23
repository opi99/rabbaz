<?php

declare(strict_types=1);

$templaterConfig = (new ForwardFW\Config\Templater\Twig())
    ->setCompilePath(__DIR__ . '/../cache/')
    ->setTemplatePath(__DIR__ . '/../src/Frontend/Templates');

return (new ForwardFW\Config\Runner\HttpMiddlewareRunner())
    ->addService(
        (new \ForwardFW\Config\Service\Logger\ChromeLogger())
    )
    ->addMiddleware(
        new \ForwardFW\Config\Middleware\Logger\ChromeLogger()
    )
    ->addMiddleware(
        (new ForwardFW\Config\Middleware\SimpleRouter())
            ->addRoute(
                (new ForwardFW\Config\Middleware\SimpleRouter\Route())
                    ->setStart('/administration')
                    ->addMiddleware(
                        (new ForwardFW\Config\Middleware\Application())
                            ->setConfig(
                                (new Rabbaz\Core\App\Config\Application())
                                    ->setName('Rabbaz Administration')
                                    ->setScreens([
                                        'dashboard' => \Rabbaz\Core\App\Controller\Screen\Dashboard::class,
                                        'displayConfiguration' => \Rabbaz\Core\App\Controller\Screen\DisplayConfiguration::class,
                                        'hardwareSearch' => \Rabbaz\Core\App\Controller\Screen\HardwareSearch::class,
                                    ])
                                    ->setTemplaterConfig($templaterConfig)
                            )
                    )
            )
            ->addRoute(
                (new ForwardFW\Config\Middleware\SimpleRouter\Route())
                    ->setStart('/display')
                    ->addMiddleware(
                        (new ForwardFW\Config\Middleware\Application())
                            ->setConfig(
                                (new Rabbaz\Core\App\Config\Application())
                                    ->setName('Rabbaz Displays')
                                    ->setScreens([
                                        'displayOutput' => \Rabbaz\Core\App\Controller\Screen\DisplayOutput::class,
                                    ])
                                    ->setTemplaterConfig($templaterConfig)
                            )
                    )
            )
            ->addRoute(
                (new ForwardFW\Config\Middleware\SimpleRouter\Route())
                    ->setStart('/')
                    ->addMiddleware(
                        (new ForwardFW\Config\Middleware\Application())
                            ->setConfig(
                                (new Rabbaz\Core\App\Config\Application())
                                    ->setName('Hello Rabbaz')
                                    ->setScreens([
                                        'Hello' => \Rabbaz\Core\App\Controller\Screen\Hello::class,
                                    ])
                                    ->setTemplaterConfig($templaterConfig)
                            )
                    )
            )
    );
