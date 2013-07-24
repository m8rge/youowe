<?php

function ModelsArrayToArrayOfArrays($value) {
    if ($value instanceof \Illuminate\Database\Eloquent\Model) {
        return $value->toArray();
    } else {
        return $value;
    }
}

$app->get(
    "/$apiVersion/debts",
    $authenticate(),
    function () use ($app) {
        $youOwe = Debt::where('sourceUserId', '=', $_SESSION['user']['id'])->get();
        $youOwe = array_map('ModelsArrayToArrayOfArrays', $youOwe);
        $IOwe = Debt::where('destUserId', '=', $_SESSION['user']['id'])->get();
        $IOwe = array_map('ModelsArrayToArrayOfArrays', $IOwe);
        echo json_encode(array('status' => 'ok', 'youOweMe' => $youOwe, 'IOweYou' => $IOwe));
    }
);

$app->post(
    "/$apiVersion/debts",
    $authenticate(),
    $requiredPostFields(array('destUserId', 'sum')),
    function () use ($app) {
        $debt = Debt::create(
            array(
                'sourceUserId' => $_SESSION['user']['id'],
                'destUserId' => $_POST['destUserId'],
                'sum' => $_POST['sum'],
            )
        );
        $app->status(201);
        echo $debt->toJson();
    }
);

$app->get(
    "/$apiVersion/debts/:id",
    $authenticate(),
    function ($id) use ($app) {
        $debt = Debt::findOrFail($id);
        if ($debt->sourceUserId != $_SESSION['user']['id'] &&
            $debt->destUserId != $_SESSION['user']['id']
        ) {
            throw new HttpException(403);
        }
        echo $debt->toJson();
    }
);

$app->delete(
    "/$apiVersion/debts/:id",
    $authenticate(),
    function ($id) use ($app) {
        $debt = Debt::findOrFail($id);
        if ($debt->sourceUserId != $_SESSION['user']['id'] &&
            $debt->destUserId != $_SESSION['user']['id']
        ) {
            throw new HttpException(403);
        }
        if ($debt->delete()) {
            $app->status(204);
        } else {
            throw new Exception('error while deleting debt model id=' . $id);
        }
    }
);

$app->post(
    "/$apiVersion/notify/:debtId",
    $authenticate(),
    function ($debtId) use ($app) {
        throw new HttpException(501);
        $debt = Debt::findOrFail($debtId);
    }
);
