<?php

$app->post(
    "/$apiVersion/login",
    $authenticate(),
    function () use ($app) {
        $app->status(204);
    }
);

$app->post(
    "/$apiVersion/logout",
    $authenticate(),
    function () use ($app, $params) {
        unset($_SESSION['user']);
        $app->status(204);
    }
);
