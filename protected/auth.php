<?php

$app->post(
    "/$apiVersion/login",
    $authenticate(),
    function () use ($app) {
        $user = User::findOrFail($_SESSION['user']['id']);
        echo $user->toJson();
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
