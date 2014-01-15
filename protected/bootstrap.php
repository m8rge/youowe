<?php

date_default_timezone_set('UTC');
require(__DIR__ . '/../vendor/autoload.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$params = require(__DIR__ . '/config/params.php');

include_once(__DIR__ . '/components/EmailNotifyHelper.php');
include_once(__DIR__ . '/components/ItsDangerous.php');
include_once(__DIR__ . '/helpers/token.php');

// setup raven
$client = new Raven_Client($params['sentryDsn']);
$error_handler = new Raven_ErrorHandler($client);
$error_handler->registerExceptionHandler();
$error_handler->registerErrorHandler();
$error_handler->registerShutdownFunction();

// setup orm
$capsule = new Capsule;
$capsule->addConnection($params['db']);
$capsule->bootEloquent();
$capsule->setAsGlobal();
Capsule::statement('SET time_zone="+00:00"');

$_SERVER['SCRIPT_NAME'] = 'index.php';
$app = new \Slim\Slim(array(
    'debug' => $params['debug'],
));
require(__DIR__ . '/app.php');
$app->run();
