<?php

/** @var \Slim\Slim $app */
/** @var mixed $params */

$app->get(
    '/',
    function () use ($app) {
        $app->render('index.twig');
    }
);
