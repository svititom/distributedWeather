<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

#$configurator->setDebugMode(True); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Asia/Singapore');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');
#$configurator->setDebugMode(False);
$container = $configurator->createContainer();

return $container;
