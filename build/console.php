#!/usr/bin/env php
<?php

require __DIR__.'/../htdocs/includes/autoload.php';
require __DIR__.'/Command/BuildModuleCommand.php';

use Dolibarr\Build\Command\BuildModuleCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BuildModuleCommand());
$application->run();
