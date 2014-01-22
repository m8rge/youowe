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
    "/$apiVersion/password-reset",
    $requiredPostFields(array('email')),
    function () use ($app, $params) {
        /** @var User $user */
        $user = User::where('email', '=', $_POST['email'])->first();
        if (!$user) {
            throw new UserException('password-emailNotExists');
        }

        $password = base64_encode(mcrypt_create_iv(12, MCRYPT_DEV_URANDOM));
        $user->password = $password;
        $user->save();

        $changePasswordToken = ItsDangerous::encode(
            array(
                'userId' => $user->id,
                'expire' => time() + 3600 * 24 * 7,
                'password' => $password
            ),
            $params['cryptSecret']
        );

        $sent = EmailNotifyHelper::passwordReset(
            $params['emailFrom'],
            $params['projectName'],
            $user->email,
            $params['projectHost'],
            $changePasswordToken
        );

        if (!$sent) {
            throw new Exception('password-emailSentFailed');
        } else {
            $app->status(204);
        }
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
