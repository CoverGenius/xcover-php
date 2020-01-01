<?php

/*
|------------------------------------------------------------------------------
| Client PHP SDK bootstrapper file
|------------------------------------------------------------------------------
| The first thing we need to do is to autoload the composer dependencies to
| achieve things like psr-4 auto loading, certain files auto loading etc.
|
| This file also initiates some of the third party libraries or packages
| e.g. Dotenv (https://github.com/vlucas/phpdotenv) integration this SDK uses.
|
*/

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use VCR\VCR;

try {
    $path = __DIR__ . '/../';
    $dotEnv = new Dotenv(__DIR__ . '/../', '.env');
} catch (InvalidPathException $e) {
    echo '.env file cannot be found';
    die(1);
} catch (InvalidFileException $e) {
    echo 'The environment file is invalid: ' . $e->getMessage();
    die(1);
}
$dotEnv->load();

VCR::configure()
    ->setCassettePath(__DIR__ . '/../tests/__fixtures__/Vcr/')
    ->enableRequestMatchers(array('method', 'url', 'host'))
    ->setStorage('json')
    ->setMode(VCR::MODE_ONCE);
VCR::turnOn();
