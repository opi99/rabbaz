<?php

/**
 * Rabbaz - Music Radio Control
 */
error_reporting(E_ALL);
ini_set("display_errors", 1);

include '../vendor/autoload.php';

$bootstrap = new ForwardFW\Bootstrap();
$bootstrap->loadConfig(__DIR__ . '/../config/Application.php');
$bootstrap->run();
