<?php

require(__DIR__ . '/../vendor/autoload.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$params = require(__DIR__ . '/config/params.php');

include_once(__DIR__ . '/components/EmailNotifyHelper.php');

// setup orm
$capsule = new Capsule;
$capsule->addConnection($params['db']);
$capsule->bootEloquent();

$_SERVER['SCRIPT_NAME'] = 'index.php';
$app = new \Slim\Slim(array(
    'debug' => $params['debug'],
));
require(__DIR__ . '/app.php');
$app->run();
