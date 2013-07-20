<?php

/** @var \Slim\Slim $app */
/** @var mixed $params */

/**
 * GLOBAL CONFIG
 */
include_once(__DIR__ . '/config/restConfig.php');
include_once(__DIR__ . '/models/User.php');
\Slim\Route::setDefaultConditions(
    array(
        'id' => '\d+',
        'debtId' => '\d+',
    )
);
$app->add(
    new \Slim\Middleware\SessionCookie(array(
        'httponly' => true,
        'expires' => 0,
        'secret' => ':j58<.\':T%4~=#l',
    ))
);
/**
 * MIDDLEWARES
 */
$authenticate = function () {
    if (empty($_SESSION['user'])) {
        throw new HttpException(403);
    }
};
$requiredPostFields = function ($fields) {
    return function () use ($fields) {
        foreach ($fields as $field) {
            if (empty($_POST[$field])) {
                throw new HttpException(400);
            }
        }
    };
};

include_once(__DIR__ . '/auth.php');
include_once(__DIR__ . '/users.php');
include_once(__DIR__ . '/debts.php');
