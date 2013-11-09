<?php

function ModelsArrayToArrayOfArrays($value) {
    if ($value instanceof \Illuminate\Database\Eloquent\Model) {
        return $value->toArray();
    } else {
        return $value;
    }
}

$app->get(
    "/$apiVersion/debts/youowe.json",
    $authenticate(),
    function () use ($app) {
        /** @var \Illuminate\Database\Eloquent\Collection $youOwe */
        $youOwe = Debt::where('destUserId', '=', $_SESSION['user']['id'])->get();
        $youOwe = array_map('ModelsArrayToArrayOfArrays', $youOwe->toArray());
        echo json_encode($youOwe);
    }
);

$app->get(
    "/$apiVersion/debts/oweyou.json",
    $authenticate(),
    function () use ($app) {
        /** @var \Illuminate\Database\Eloquent\Collection $IOwe */
        $IOwe = Debt::where('sourceUserId', '=', $_SESSION['user']['id'])->get();
        $IOwe = array_map('ModelsArrayToArrayOfArrays', $IOwe->toArray());
        echo json_encode($IOwe);
    }
);

$app->post(
    "/$apiVersion/debts",
    $authenticate(),
    $requiredPostFields(array('sum')),
    function () use ($app, $params) {
        if (empty($_POST['destUserId']) && empty($_POST['email'])) {
            throw new UserException('Wrong debt recipient');
        }

        if (!empty($_POST['email'])) {
            if (User::where('email', '=', $_POST['email'])->count()) {
                throw new UserException('email already exists');
            }
            /** @var User $destUser */
            $destUser = User::create(
                array(
                    'email' => $_POST['email'],
                    'password' => mcrypt_create_iv(12, MCRYPT_DEV_URANDOM),
                )
            );
        } else {
            $destUser = User::findOrFail($_POST['destUserId']);
        }
        $debt = Debt::create(
            array(
                'sourceUserId' => $_SESSION['user']['id'],
                'destUserId' => $destUser->id,
                'sum' => $_POST['sum'],
            )
        );

        $app->status(201);
        echo $debt->toJson();
    }
);

$app->get(
    "/$apiVersion/debts/:id.json",
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
    "/$apiVersion/debts/:id.json",
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
