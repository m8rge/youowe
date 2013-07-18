<?php

require(__DIR__ . '/../vendor/autoload.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$params = require(__DIR__ . '/config/params.php');

// setup orm
$capsule = new Capsule;
$capsule->addConnection($params['db']);
$capsule->bootEloquent();

$_SERVER['SCRIPT_NAME'] = 'index.php';
$app = new \Slim\Slim();
require(__DIR__ . '/app.php');
$app->run();
