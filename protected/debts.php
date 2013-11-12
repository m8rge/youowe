<?php

use Illuminate\Database\Query\Expression;

function ModelsArrayToArrayOfArrays($value) {
    if ($value instanceof \Illuminate\Database\Eloquent\Model) {
        return $value->toArray();
    } else {
        return $value;
    }
}

$app->get(
    "/$apiVersion/debts/debts.json",
    $authenticate(),
    function () use ($app) {
        $youTake = Debt::query()->getQuery()
            ->where('destUserId', '=', $_SESSION['user']['id'])
            ->groupBy('sourceUserId')
            ->orderBy('sum', 'desc')
            ->get(array(new Expression('sourceUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youTakeSums = array();
        foreach ($youTake as $user) {
            $youTakeSums[$user['userId']] = $user['sum'];
        }

        $youGave = Debt::query()->getQuery()
            ->where('sourceUserId', '=', $_SESSION['user']['id'])
            ->groupBy('destUserId')
            ->get(array(new Expression('destUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youGaveSums = array();
        foreach ($youGave as $user) {
            $youGaveSums[$user['userId']] = (int)$user['sum'];
        }

        foreach ($youTakeSums as $userId => $sum) {
            if (!empty($youGaveSums[$userId])) {
                if ($sum - $youGaveSums[$userId] > 0) {
                    $youTakeSums[$userId] -= $youGaveSums[$userId];
                    unset($youGaveSums[$userId]);
                } else {
                    $youGaveSums[$userId] -= $youTakeSums[$userId];
                    unset($youTakeSums[$userId]);
                }
            }
        }

        echo json_encode( array(
            'youTake' => $youTakeSums,
            'youGave' => $youGaveSums,
        ), JSON_FORCE_OBJECT);
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

//$app->get(
//    "/$apiVersion/debts/:id.json",
//    $authenticate(),
//    function ($id) use ($app) {
//        $debt = Debt::findOrFail($id);
//        if ($debt->sourceUserId != $_SESSION['user']['id'] &&
//            $debt->destUserId != $_SESSION['user']['id']
//        ) {
//            throw new HttpException(403);
//        }
//        echo $debt->toJson();
//    }
//);

//$app->delete(
//    "/$apiVersion/debts/:id.json",
//    $authenticate(),
//    function ($id) use ($app) {
//        $debt = Debt::findOrFail($id);
//        if ($debt->sourceUserId != $_SESSION['user']['id'] &&
//            $debt->destUserId != $_SESSION['user']['id']
//        ) {
//            throw new HttpException(403);
//        }
//        if ($debt->delete()) {
//            $app->status(204);
//        } else {
//            throw new Exception('error while deleting debt model id=' . $id);
//        }
//    }
//);

$app->post(
    "/$apiVersion/notify/:debtId",
    $authenticate(),
    function ($debtId) use ($app) {
        throw new HttpException(501);
        $debt = Debt::findOrFail($debtId);
    }
);
