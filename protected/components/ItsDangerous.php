<?php

class ItsDangerous
{
    /**
     * @param string $string
     * @return mixed|string
     */
    protected static function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    /**
     * @param string $string
     * @return string
     */
    protected static function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * @param string $value
     * @param string $secretKey
     * @return bool|string
     */
    public static function encode($value, $secretKey)
    {
        if (!$value) {
            return false;
        }
        $text = json_encode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        $cryptText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secretKey, $text, MCRYPT_MODE_ECB, $iv);
        return trim(self::safe_b64encode($cryptText));
    }

    /**
     * @param string $value
     * @param string $secretKey
     * @return null|mixed
     */
    public static function decode($value, $secretKey)
    {
        if (!$value) {
            return null;
        }
        $cryptText = self::safe_b64decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        $decryptText = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secretKey, $cryptText, MCRYPT_MODE_ECB, $iv);
        return json_decode(trim($decryptText), true);
    }

    /**
     * @param string $data
     * @param string $secretKey
     * @return string
     */
    public static function signData($data, $secretKey)
    {
        $hash = hash_hmac('sha256', $data, $secretKey);

        return $hash . $data;
    }

    /**
     * @param string $data
     * @param $secretKey
     * @internal param string $cryptKey
     * @internal param string $hashKey
     * @return null|string
     */
    public static function unsignData($data, $secretKey)
    {
        $hash = substr($data, 0, 64);

        $computedHash = hash_hmac('sha256', $data, $secretKey);
        if ($hash == $computedHash) {
            return substr($data, 64);
        }

        return null;
    }
}
