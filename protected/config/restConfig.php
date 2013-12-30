<?php

/** @var \Slim\Slim $app */

include_once(__DIR__ . '/../components/HttpException.php');
include_once(__DIR__ . '/../components/UserException.php');

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
            echo json_encode(array('status' => $status, 'error' => $userExceptionCode));
        } else {
            echo json_encode(array('status' => $status));
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
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
            $postBody = file_get_contents('php://input');
            $_POST = json_decode($postBody, true);
        }

        $app->contentType('application/json');
    }
);