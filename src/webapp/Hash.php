<?php

namespace tdt4237\webapp;

class Hash
{
    const ITERATIONS = 1000;
    const LENGTH = 20;

    function __construct()
    {
    }

    static function make($plaintext, $salt)
    {
        return hash_pbkdf2('sha512', $plaintext, $salt, self::ITERATIONS, self::LENGTH);
    }

    static function createSalt()
    {
        $salt = md5(uniqid(mt_rand(), TRUE));
        return $salt;
    }

    static function check($plaintext, $salt, $hash)
    {
        return self::make($plaintext, $salt) === $hash;
    }
}
