<?php

class UserException extends Exception
{
    /**
     * @var int
     */
    protected $userExceptionCode;

    public function __construct($userExceptionCode, $code = 400, Exception $previous = null)
    {
        $this->userExceptionCode = $userExceptionCode;

        parent::__construct('', $code, $previous);
    }

    /**
     * @return int
     */
    public function getUserExceptionCode()
    {
        return $this->userExceptionCode;
    }
}