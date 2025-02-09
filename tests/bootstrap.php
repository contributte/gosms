<?php declare(strict_types = 1);

use Contributte\Tester\Environment;
use Tester\Environment as TesterEnvironment;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

Environment::setup(__DIR__);
TesterEnvironment::bypassFinals();
