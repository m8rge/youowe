<?php

require(__DIR__ . '/../vendor/autoload.php');

use Illuminate\Database\Capsule\Manager as Capsule;

$params = require(__DIR__ . '/config/params.php');

// setup orm
$capsule = new Capsule;
$capsule->addConnection($params['db']);
$capsule->bootEloquent();

// setup slim with twig
\Slim\Extras\Views\Twig::$twigOptions = array(
    'cache' => __DIR__ . '/templates/cache',
    'auto_reload' => true,
);
$twigView = new \Slim\Extras\Views\Twig();
$app = new \Slim\Slim(array(
    'templates.path' => __DIR__ . '/templates',
    'view' => $twigView,
));

require(__DIR__ . '/app.php');

$app->run();
