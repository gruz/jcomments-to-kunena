#!/usr/bin/env php
<?php

const _JEXEC = 1;

function includeIfExists($file, $scope)
{
    if (file_exists($file)) {
        if ('joomla' === $scope) {
            define('JPATH_BASE', dirname($file));
        }
        return require_once $file;
    } else {
        return false;
    }
}

$paths = [
    'composer' => './vendor/autoload.php',
    'joomla' => './../../includes/defines.php',
];

foreach ($paths as $scope => $path) {
    $files = [ $path, './../.' .$path ];
    $managedToLoad = false;
    foreach ($files as $file) {
        if (includeIfExists($file, $scope)) {
            $managedToLoad = true;
            break;
        }
    }

    if (! $managedToLoad) {
        break;
    }
}


if (! $managedToLoad) {
    if ('composer' === $scope) {
        $msg =
            'You must set up the project dependencies, run the following commands:'.PHP_EOL.
            'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
            'php composer.phar install'.PHP_EOL;
    } else {
        $msg = 'Cannot find joomla file:' . basename($path) .PHP_EOL;
    }
    fwrite(STDERR, $msg);
    exit(1);
}

// if (!defined('JDEBUG')) {
//     define('JDEBUG', false);
// }

// Load the rest of the framework include files
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php')) {
    require_once JPATH_LIBRARIES . '/import.legacy.php';
} else {
    require_once JPATH_LIBRARIES . '/import.php';
}

require_once JPATH_LIBRARIES . '/cms.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');
JLoader::import('joomla.application.component.helper');
JLoader::import('cms.component.helper');

error_reporting(E_ALL);
ini_set('display_errors', 1);
