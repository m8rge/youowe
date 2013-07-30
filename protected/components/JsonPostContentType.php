<?php namespace Slim\Middleware;

class JsonPostContentType extends \Slim\Middleware
{
    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    public function call()
    {
        $postBody = file_get_contents('php://input');
        $_POST = json_decode($postBody, true);
        $this->next->call();
    }
}