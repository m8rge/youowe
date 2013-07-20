<?php

$app->get(
    "/$apiVersion/debts",
    $authenticate,
    function () use ($app) {
        //my debts
    }
);

$app->post(
    "/$apiVersion/debts",
    $authenticate,
    $requiredPostFields(array('destUserId', 'sum')),
    function () use ($app) {
    }
);

$app->get(
    "/$apiVersion/debts/:id",
    $authenticate,
    function ($id) use ($app) {
    }
);

$app->delete(
    "/$apiVersion/debts/:id",
    $authenticate,
    function ($id) use ($app) {
    }
);

$app->post(
    "/$apiVersion/notify/:debtId",
    $authenticate,
    function ($debtId) use ($app) {
    }
);
