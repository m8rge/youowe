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
    $authenticate(),
    function () use ($app) {
        $users = User::all(array('id', 'email'));
        echo $users->toJson();
    }
);

$app->post(
    "/$apiVersion/users",
    $requiredPostFields(array('email')),
    function () use ($app) {
        if (User::where('email', '=', $_POST['email'])->count()) {
            throw new UserException('email already exists');
        }

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
        $app->status(201);
        echo $user->toJson();
    }
);

$app->put(
    "/$apiVersion/users/:id",
    $authenticate(),
    $requiredPostFields(array('password')),
    function ($id) use ($app) {
        if ($_SESSION['user']['id'] != $id) {
            throw new HttpException(403);
        }
        $user = User::findOrFail($id);
        $user->password = $_POST['password'];
        $user->save();
        $app->status(204);
    }
);
