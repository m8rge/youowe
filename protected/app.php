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
