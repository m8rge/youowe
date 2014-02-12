<?php

$app->get(
    "/$apiVersion/users.json",
    $authenticate(),
    function () use ($app) {
        $users = User::where('id', '!=', $_SESSION['user']['id'])->get(array('id', 'email', 'nickname', 'hipchatMentionName'));
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
            throw new UserException('register-emailAlreadyExists');
        }
        $user = User::create($_POST);
        $app->status(201);
        echo $user->toJson();
    }
);

$app->post(
    "/$apiVersion/users/:userId",
    $authenticate(),
    $requiredPostFields(array('nickname', 'email')),
    function ($userId) use ($app) {
        if ($_SESSION['user']['id'] != $userId) {
            throw new HttpException(403);
        }
        /** @var User $user */
        $user = User::findOrFail($userId);
        $userAttributes = $user->getFillable();
        foreach ($_POST as $attribute => $value) {
            if (in_array($attribute, $userAttributes)) {
                if (empty($value)) {
                    $value = null;
                }
                $user->setAttribute($attribute, $value);
            }
        }

        checkForUniqueFields($user);
        $user->save();
        echo $user->toJson();
    }
);

$app->post(
    "/$apiVersion/updateProfile/:token",
    $requiredPostFields(array('nickname', 'email', 'password')),
    function ($token) use ($app, $params) {
        $user = decodeToken($token);
        $userAttributes = $user->getFillable();
        foreach ($_POST as $attribute => $value) {
            if (in_array($attribute, $userAttributes)) {
                $user->setAttribute($attribute, $value);
            }
        }

        checkForUniqueFields($user);
        $user->save();
        echo $user->toJson();
    }
);

function checkForUniqueFields(User $user)
{
    $changedAttributes = $user->getDirty();
    if (!empty($changedAttributes['hipchatMentionName']) &&
        User::where('hipchatMentionName', '=', $changedAttributes['hipchatMentionName'])->count() == 1) {
        throw new UserException('register-hipchatMentionNameAlreadyExists');
    }
    if (!empty($changedAttributes['email']) &&
        User::where('email', '=', $changedAttributes['email'])->count() == 1) {
        throw new UserException('register-hipchatEmailAlreadyExists');
    }
}