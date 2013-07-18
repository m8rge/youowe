<?php

/** @var \Slim\Slim $app */
/** @var mixed $params */

$app->get(
    '/hello',
    function () use ($app) {
        echo "hello, world";
    }
);
