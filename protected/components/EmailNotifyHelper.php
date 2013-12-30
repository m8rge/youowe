<?php

use m8rge\AlternativeMail;

class EmailNotifyHelper
{
    public static function newDebtNotify($from, $fromName, $to, $sourceUserTitle, $sum, $projectHost, $changePasswordToken = null)
    {
        $requisites = '';
        if (!empty($changePasswordToken)) {
            $changePasswordToken = urlencode($changePasswordToken);
            $requisites = "\n\nВаши реквизиты для входа:
Email: {$to}
Пароль: Вы можете установить по ссылке http://{$projectHost}/#/changePassword/$changePasswordToken";
        }

        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($from, $fromName)
            ->setSubject("Вы должны {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} дал Вам в долг {$sum}р." . $requisites . self::getFooter());
        return $mail->send();
    }

    public static function debtNotify($from, $fromName, $to, $sourceUserTitle, $sum)
    {
        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($from, $fromName)
            ->setSubject("Вы должны {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} напоминает, что Вы должны ему {$sum}р." . self::getFooter());
        return $mail->send();
    }

    protected static function getFooter()
    {
        return "\n\nYouowe.";
    }
} 