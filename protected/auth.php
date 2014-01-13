<?php

$app->post(
    "/$apiVersion/login",
    function () use ($app) {
        if (empty($_SESSION['user'])) {
            $user = User::where('email', '=', $_POST['email'])->first();
            if (empty($user) || !password_verify($_POST['password'], $user['hashedPassword'])) {
                throw new UserException('Invalid login', 401);
            }

            $_SESSION['user']['id'] = $user['id'];
        } else {
            $user = User::findOrFail($_SESSION['user']['id']);
        }
        echo $user->toJson();
    }
);

$app->get(
    "/$apiVersion/decodeToken/:token",
    function ($token) use ($app, $params) {
        $user = decodeToken($token);
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
