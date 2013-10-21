<?php

$app->get(
    "/$apiVersion/users.json",
    $authenticate(),
    function () use ($app) {
        $users = User::all(array('id', 'email'));
        echo $users->toJson();
    }
);

$app->post(
    "/$apiVersion/users",
    $requiredPostFields(array('email', 'password')),
    function () use ($app) {
        if (User::where('email', '=', $_POST['email'])->count()) {
            throw new UserException('email already exists');
        }
        $user = User::create(
            array(
                'email' => $_POST['email'],
                'password' => $_POST['password'],
            )
        );
        $app->status(201);
        echo $user->toJson();
    }
);

$app->put(
    "/$apiVersion/users/:id.json",
    $authenticate(),
    $requiredPostFields(array('password')),
    function ($id) use ($app) {
        if ($_SESSION['user']['id'] != $id) {
            throw new HttpException(403);
        }
        /** @var User $user */
        $user = User::findOrFail($id);
        $user->password = $_POST['password'];
        $user->save();
        $app->status(204);
    }
);
