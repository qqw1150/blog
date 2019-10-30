<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerNamespaces(
    [
        'app\controllers' => APP_PATH.'/controllers/',
        'app\models'      => APP_PATH.'/models/',
        'app\validations'      => APP_PATH.'/validations/',
        'app\exceptions'      => APP_PATH.'/exceptions/',
        'app\services'      => APP_PATH.'/services/',
        'app\libraries'      => APP_PATH.'/libraries/',
    ]
)->register();
