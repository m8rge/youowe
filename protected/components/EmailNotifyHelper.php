<?php

use m8rge\AlternativeMail;

class EmailNotifyHelper
{
    public static function newDebtNotify($from, $fromName, $to, $sourceUserTitle, $sum, $description, $projectHost, $changePasswordToken = null)
    {
        $requisites = '';
        if (!empty($changePasswordToken)) {
            $changePasswordToken = urlencode($changePasswordToken);
            $requisites = "\n\nВаши реквизиты для входа:
Email: {$to}
Пароль: Вы можете установить по ссылке http://{$projectHost}/#/changePassword/$changePasswordToken";
        }

        if (!empty($description)) {
            $description = " с комментарием: $description";
        }

        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($from, $fromName)
            ->setSubject("Вы получили {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} дал Вам {$sum}р$description." . $requisites . self::getFooter($projectHost));
        return $mail->send();
    }

    public static function debtNotify($from, $fromName, $to, $sourceUserTitle, $sum, $projectHost)
    {
        $mail = new AlternativeMail();
        $mail->addTo($to)
            ->setFrom($from, $fromName)
            ->setSubject("Вы должны {$sum}р.")
            ->setTextBody("Пользователь {$sourceUserTitle} напоминает, что Вы должны ему {$sum}р." . self::getFooter($projectHost));
        return $mail->send();
    }

    protected static function getFooter($projectHost)
    {
        return "\n\n--\nhttp://{$projectHost}\nYouowe.";
    }
} 