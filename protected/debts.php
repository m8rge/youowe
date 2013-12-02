<?php

use Illuminate\Database\Query\Expression;

function ModelsArrayToArrayOfArrays($value)
{
    if ($value instanceof \Illuminate\Database\Eloquent\Model) {
        return $value->toArray();
    } else {
        return $value;
    }
}

$app->get(
    "/$apiVersion/debts/summary.json",
    $authenticate(),
    function () use ($app) {
        $youTook = Debt::query()->getQuery()
            ->where('destUserId', '=', $_SESSION['user']['id'])
            ->groupBy('sourceUserId')
            ->get(array(new Expression('sourceUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youTookSums = array();
        foreach ($youTook as $user) {
            $youTookSums[$user['userId']] = $user['sum'];
        }

        $youGave = Debt::query()->getQuery()
            ->where('sourceUserId', '=', $_SESSION['user']['id'])
            ->groupBy('destUserId')
            ->get(array(new Expression('destUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youGaveSums = array();
        foreach ($youGave as $user) {
            $youGaveSums[$user['userId']] = (int)$user['sum'];
        }

        foreach ($youTookSums as $userId => $sum) {
            if (!empty($youGaveSums[$userId])) {
                if ($sum - $youGaveSums[$userId] > 0) {
                    $youTookSums[$userId] -= $youGaveSums[$userId];
                    unset($youGaveSums[$userId]);
                } else {
                    $youGaveSums[$userId] -= $youTookSums[$userId];
                    unset($youTookSums[$userId]);
                }
            }
        }

        echo json_encode(
            array(
                'youTook' => $youTookSums,
                'youGave' => $youGaveSums,
            ),
            JSON_FORCE_OBJECT
        );
    }
);

$app->post(
    "/$apiVersion/debts",
    $authenticate(),
    $requiredPostFields(array('sum')),
    function () use ($app, $params) {
        if (empty($_POST['destUserId']) && empty($_POST['email'])) {
            throw new UserException('newDebt-wrongRecipient');
        }

        if (!empty($_POST['email'])) {
            if (User::where('email', '=', $_POST['email'])->count()) {
                throw new UserException('newDebt-emailExists');
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
    "/$apiVersion/debts/history/:id.json",
    $authenticate(),
    function ($id) use ($app) {
        $history = Debt::query()->getQuery()
            ->where('destUserId', '=', $_SESSION['user']['id'])
            ->where('sourceUserId', '=', $id)
            ->where('sourceUserId', '=', $_SESSION['user']['id'], 'or')
            ->where('destUserId', '=', $id)
            ->orderBy('createdDate', 'desc')
            ->get(array('destUserId', 'sourceUserId', 'sum', new Expression('UNIX_TIMESTAMP(createdDate) as createdDate')));

        echo json_encode(
            $history,
            JSON_FORCE_OBJECT
        );
    }
);

$app->post(
    "/$apiVersion/notify/:userId",
    $authenticate(),
    function ($userId) use ($app, $params) {
        $youGave = Debt::query()->getQuery()
            ->where('sourceUserId', '=', $_SESSION['user']['id'])
            ->where('destUserId', '=', $userId)
            ->sum('sum');
        $youTook = Debt::query()->getQuery()
            ->where('sourceUserId', '=', $userId)
            ->where('destUserId', '=', $_SESSION['user']['id'])
            ->sum('sum');
        $sum = $youGave - $youTook;

        if ($sum < 0) {
            throw new UserException('notify-youOwe');
        } elseif ($sum == 0) {
            throw new UserException('notify-zeroDebt');
        }

        /** @var User $me */
        $me = User::findOrFail($_SESSION['user']['id']);
        /** @var User $destUser */
        $destUser = User::findOrFail($userId);

        $sent = EmailNotifyHelper::debtNotify($params['emailFrom'], $destUser->email, $me->getTitle(), $sum);

        if (!$sent) {
            throw new Exception('notify-emailSentFailed');
        } else {
            $app->status(204);
        }
    }
);
