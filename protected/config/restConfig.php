<?php

/** @var \Slim\Slim $app */

include_once(__DIR__ . '/../components/HttpException.php');

$apiVersion = 'v1';
$app->error(
    function (\Exception $e) use ($app) {
        if ($e instanceof HttpException || $e instanceof UserException) {
            /** @var Exception $e */
            $status = $e->getCode() ? $e->getCode() : 500;
            $app->response()->status($status);
            echo json_encode(
                array(
                    'status' => $status,
                    'message' => $e->getMessage(),
                )
            );
        } else {
            echo json_encode(
                array(
                    'status' => 500,
                    'message' => 'An error has occurred',
                )
            );
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
