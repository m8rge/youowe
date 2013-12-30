<?php

$app->get(
    "/$apiVersion/users.json",
    $authenticate(),
    function () use ($app) {
        $users = User::where('id', '!=', $_SESSION['user']['id'])->get(array('id', 'email', 'nickname'));
        $result = array();
        /** @var User $user */
        foreach ($users as $user) {
            $result[ $user->id ] = $user->toArray();
        }

        echo json_encode($result);
    }
);

$app->post(
    "/$apiVersion/users",
    $requiredPostFields(array('nickname', 'email', 'password')),
    function () use ($app) {
        if (User::where('email', '=', $_POST['email'])->count()) {
            throw new UserException('register-emailExists');
        }
        $user = User::create($_POST);
        $app->status(201);
        echo $user->toJson();
    }
);

$app->post(
    "/$apiVersion/users/:userId",
    $authenticate(),
    $requiredPostFields(array('nickname', 'email', 'nickname')),
    function ($userId) use ($app) {
        if ($_SESSION['user']['id'] != $userId) {
            throw new HttpException(403);
        }
        /** @var User $user */
        $user = User::findOrFail($userId);
        foreach ($_POST as $attribute => $value) {
            $user->setAttribute($attribute, $value);
        }
        $user->save();
        echo $user->toJson();
    }
);

$app->post(
    "/$apiVersion/updateProfile/:token",
    $requiredPostFields(array('nickname', 'email', 'password')),
    function ($token) use ($app, $params) {
        $user = decodeToken($token);
        foreach ($_POST as $attribute => $value) {
            $user->setAttribute($attribute, $value);
        }
        $user->save();
        echo $user->toJson();
    }
);

