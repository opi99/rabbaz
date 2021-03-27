<?php

$templaterConfig = (new ForwardFW\Config\Templater\Twig())
    ->setCompilePath(__DIR__ . '/../cache/')
    ->setTemplatePath(__DIR__ . '/../Frontend/Templates');

return (new ForwardFW\Config\Runner())
        ->addProcessor(
            (new ForwardFW\Config\Filter\RequestResponse\SimpleRouter())
                ->addRoute(
                    (new ForwardFW\Config\Filter\RequestResponse\SimpleRouter\Route())
                        ->setStart('/administration')
                        ->addFilter(
                            (new ForwardFW\Config\Filter\RequestResponse\Application())
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
                    (new ForwardFW\Config\Filter\RequestResponse\SimpleRouter\Route())
                        ->setStart('/display')
                        ->addFilter(
                            (new ForwardFW\Config\Filter\RequestResponse\Application())
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
                    (new ForwardFW\Config\Filter\RequestResponse\SimpleRouter\Route())
                        ->setStart('/')
                        ->addFilter(
                            (new ForwardFW\Config\Filter\RequestResponse\Application())
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
