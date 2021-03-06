<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_file($autoloadFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \RuntimeException('Could not find vendor/autoload.php. Did you run "composer install --dev"?');
}

$loader = require $autoloadFile;
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
