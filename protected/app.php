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
        'expires' => time() + 3600*24*7,
        'secret' => $params['cookieSecret'],
    ))
);
/**
 * MIDDLEWARES
 */
$authenticate = function() use ($app, $params) {
    return function () use ($app, $params) {
        if (!empty($_POST['token']) && !empty($_POST['sourceUserMention'])) {
            if ($_POST['token'] === $params['apiKey']) {
                $user = User::where('hipchatMentionName', '=', $_POST['sourceUserMention'])->first();
                if (!empty($user)) {
                    $_SESSION['user']['id'] = $user['id'];
                    unset($_POST['sourceUserMention']);
                } else {
                    throw new UserException("source user not found", 403);
                }
                unset($user);
                $user = User::where('hipchatMentionName', '=', $_POST['destUserMention'])->first();
                if (!empty($user)) {
                    $_POST['destUserId'] = $user['id'];
                    unset($_POST['destUserMention']);
                }
            } else {
                throw new HttpException(403);
            }
        }
        if (empty($_SESSION['user'])) {
            throw new HttpException(401);
        }
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
