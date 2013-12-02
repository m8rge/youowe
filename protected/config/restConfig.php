<?php

/** @var \Slim\Slim $app */

include_once(__DIR__ . '/../components/UserException.php');
include_once(__DIR__ . '/../components/JsonPostContentType.php');

$apiVersion = 'v1';
$app->error(
    function (\Exception $e) use ($app) {
        $userExceptionCode = null;
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $status = 404;
        } elseif ($e instanceof HttpException) {
            $status = $e->getMessage();
        } elseif ($e instanceof UserException) {
            $status = $e->getCode();
            $userExceptionCode = $e->getUserExceptionCode();
        } else {
            $status = 500;
        }

        $app->status($status);
        if (!is_null($userExceptionCode)) {
            echo json_encode(['status' => $status, 'error' => $userExceptionCode]);
        } else {
            echo json_encode(['status' => $status]);
        }
    }
);
$app->notFound(
    function () use ($app) {
        throw new HttpException(404);
    }
);
$app->hook(
    'slim.before',
    function () use ($app) {
        $app->contentType('application/json');
    }
);
$app->add(new \Slim\Middleware\JsonPostContentType());
