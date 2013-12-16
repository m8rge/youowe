<?php

use m8rge\AlternativeMail;

class EmailNotifyHelper
{
    public static function newDebtNotify($from, $to, $sourceUserTitle, $sum, $projectHost, $changePasswordToken = null)
    {
//        if (!empty($changePasswordToken)) {
//            $changePasswordToken = urlencode($changePasswordToken);
//            $requisites = "
//Ваши реквизиты для входа:
//Email: {$to}
//Пароль: Вы можете установить по ссылке http://{$projectHost}/changePassword?token=$changePasswordToken
//";
//        } else {
//            $requisites = '';
//        }
//
//
//        $mail = new AlternativeMail();
//        $mail->addTo($to)
//            ->setFrom($from)
//            ->setSubject('Вам дали в долг');
//        $mail->setTextBody("Пользователь {$sourceUserTitle} дал Вам в долг {$sum}р.
//
//Вы согласны с этим?
//Да - http://{$projectHost}/debt/{$debtId}/approve
//Нет - http://{$projectHost}/debt/{$debtId}/decline
//$requisites
//Youowe.");
//        return $mail->send();
    }

    public static function debtNotify($from, $fromName, $to, $sourceUserTitle, $sum)
    {
        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($from, $fromName)
            ->setSubject("Вы должны {$sum}р.");
        $mail->setTextBody("Пользователь {$sourceUserTitle} напоминает, что Вы должны ему {$sum}р." . self::getFooter());
        return $mail->send();
    }

    protected static function getFooter()
    {
        return "\n\nYouowe.";
    }
} 