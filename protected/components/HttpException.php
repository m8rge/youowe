<?php

class HttpException extends Exception
{
    public function __construct($code = 0, $message = "", Exception $previous = null)
    {
        if (empty($message)) {
            $message = \Slim\Http\Response::getMessageForCode($code);
            if (!empty($message)) {
                $message = substr($message, strpos($message, ' ') + 1);
            }
        }
        parent::__construct($message, $code, $previous);
    }
}