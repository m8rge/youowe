<?php

/**
 * @param string $token
 * @return User
 * @throws UserException
 */
function decodeToken($token)
{
    global $params;

    /** @noinspection PhpUndefinedVariableInspection */
    $data = ItsDangerous::decode($token, $params['cryptSecret']);
    if (is_null($data)) {
        throw new UserException('token-wrong');
    }
    if (time() > $data['expire']) {
        throw new UserException('token-expired');
    }
    try {
        /** @var User $user */
        $user = User::findOrFail($data['userId']);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        throw new UserException('token-userNotFound');
    }
    if (!password_verify($data['password'], $user['hashedPassword'])) {
        throw new UserException('token-used');
    }

    return $user;
}
