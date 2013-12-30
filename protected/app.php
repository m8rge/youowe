<?php

/** @var \Slim\Slim $app */
/** @var mixed $params */

/**
 * GLOBAL CONFIG
 */
include_once(__DIR__ . '/config/restConfig.php');
include_once(__DIR__ . '/models/User.php');
include_once(__DIR__ . '/models/Debt.php');
\Slim\Route::setDefaultConditions(
    array(
        'id' => '\d+',
        'debtId' => '\d+',
        'userId' => '\d+',
    )
);
$app->add(
    new \Slim\Middleware\SessionCookie(array(
        'httponly' => true,
        'expires' => 0,
        'secret' => $params['cookieSecret'],
    ))
);
/**
 * MIDDLEWARES
 */
$authenticate = function() use ($app) {
    return function () use ($app) {
        $username = $app->request()->headers('PHP_AUTH_USER');
        $password = $app->request()->headers('PHP_AUTH_PW');
        if (empty($username) && empty($password) && empty($_SESSION['user'])) {
            $app->response()->header('WWW-Authenticate', 'Basic realm="You must authenticate"');
            throw new HttpException(401);
        }
        $user = User::where('email', '=', $username)->first();
        if (empty($user) || !password_verify($password, $user['hashedPassword'])) {
            $app->response()->header('WWW-Authenticate', 'Basic realm="You must authenticate"');
            throw new UserException('Invalid login', 401);
        }
        $_SESSION['user']['id'] = $user['id'];
    };
};
$requiredPostFields = function ($fields) {
    return function () use ($fields) {
        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                throw new UserException('wrongPostFields');
            }
        }
    };
};

include_once(__DIR__ . '/auth.php');
include_once(__DIR__ . '/users.php');
include_once(__DIR__ . '/debts.php');
