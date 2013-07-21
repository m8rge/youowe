<?php

function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

$app->get(
    "/$apiVersion/users",
    $authenticate,
    function () use ($app) {
        $users = User::all(array('id', 'email'));
        echo json_encode(array('status' => 'ok', 'items' => $users->toArray()));
    }
);

$app->post(
    "/$apiVersion/users",
    $requiredPostFields(array('email')),
    function () use ($app) {
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
        } else {
            $password = generatePassword();
        }
        $user = User::create(
            array(
                'email' => $_POST['email'],
                'password' => $password,
            )
        );
        echo json_encode(array('status' => 'ok', 'id' => $user['id']));
    }
);

$app->put(
    "/$apiVersion/users/:id",
    $authenticate,
    $requiredPostFields(array('password')),
    function () use ($app) {
    }
);
