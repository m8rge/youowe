<?php

$app->post(
    "/$apiVersion/login",
    $requiredPostFields(array('email', 'password')),
    function () use ($app) {
        $user = User::where('email', '=', $_POST['email'])->first();
        if (!empty($user)) {
            $_SESSION['user']['id'] = $user['id'];
            echo json_encode(array('status' => 'ok'));
        } else {
            throw new UserException('user not found');
        }
    }
);

$app->post(
    "/$apiVersion/logout",
    $authenticate,
    $requiredPostFields(array('hash')),
    function () use ($app, $params) {
        if (sha1($_SESSION['user']['id'] . $params['logoutSalt']) !== $_POST['hash']) {
            throw new UserException('wrong hash value');
        }
        unset($_SESSION['user']);
        echo json_encode(array('status' => 'ok'));
    }
);
