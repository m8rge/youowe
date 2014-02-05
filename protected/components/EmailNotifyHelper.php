<?php

use m8rge\AlternativeMail;
use Illuminate\Database\Query\Expression;

class EmailNotifyHelper
{
    public static function getBalanceWithUser($userId)
    {
        $youTook = Debt::query()->getQuery()
            ->where('destUserId', '=', $_SESSION['user']['id'])
            ->where('sourceUserId', '=', $userId)
            ->get(array(new Expression('sourceUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youTookSum = 0;
        $user = reset($youTook);
        if (!empty($user)) {
            $youTookSum = (int)$user['sum'];
        }

        $youGave = Debt::query()->getQuery()
            ->where('sourceUserId', '=', $_SESSION['user']['id'])
            ->where('destUserId', '=', $userId)
            ->groupBy('destUserId')
            ->get(array(new Expression('destUserId as userId'), new Expression('sum(`sum`) as `sum`')));
        $youGaveSum = 0;
        $user = reset($youGave);
        if (!empty($user)) {
            $youGaveSum = (int)$user['sum'];
        }

        return $youGaveSum - $youTookSum;
    }

    /**
     * @param $params
     * @param User $destUser
     * @param $sourceUserTitle
     * @param $sum
     * @param $description
     * @param null $changePasswordToken
     * @return bool
     */
    public static function newDebtNotify($params, $destUser, $sourceUserTitle, $sum, $description, $changePasswordToken = null)
    {
        if (!empty($changePasswordToken)) {
            $changePasswordToken = urlencode($changePasswordToken);
            $append = "\n\nВаши реквизиты для входа:
Email: {$destUser->email}
Пароль: Вы можете установить по ссылке http://{$params['projectHost']}/#/changePassword/$changePasswordToken";
        } else {
            $balance = self::getBalanceWithUser($destUser->id);
            if ($balance > 0) {
                $append = "\nТеперь Вы должны {$sourceUserTitle} {$balance}р.";
            } elseif ($balance < 0) {
                $balance = -1*$balance;
                $append = "\nТеперь {$sourceUserTitle} должен Вам {$balance}р.";
            } else {
                $append = "\nТеперь вы в расчете.";
            }
        }

        if (!empty($description)) {
            $description = " с комментарием: $description.";
        }

        $mail = new AlternativeMail();
        $mail->addTo($destUser->email)
            ->setFrom($params['emailFrom'], $params['projectName'])
            ->setSubject("Вы получили {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} дал Вам {$sum}р.$description" . $append . self::getFooter($params['projectHost']));
        return $mail->send();
    }

    public static function passwordReset($params, $to, $changePasswordToken)
    {
        $changePasswordToken = urlencode($changePasswordToken);

        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($params['emailFrom'], $params['projectName'])
            ->setSubject("Сброс пароля для Youowe")
            ->setTextBody("Новый пароль Вы можете установить по ссылке http://{$params['projectHost']}/#/changePassword/$changePasswordToken" . self::getFooter($params['projectHost']));
        return $mail->send();
    }

    public static function debtNotify($params, $to, $sourceUserTitle, $sum)
    {
        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($params['emailFrom'], $params['projectName'])
            ->setSubject("Вы должны {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} напоминает, что Вы должны ему {$sum}р." . self::getFooter($params['projectHost']));
        return $mail->send();
    }

    protected static function getFooter($projectHost)
    {
        return "\n\n--\nhttp://{$projectHost}\nYouowe.";
    }
} 