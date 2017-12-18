<?php

namespace AppBundle\Service;

/**
 * Class CryptService
 * @package AppBundle\Service
 *
 */

class CryptService
{
    public function compare($encryptedReference, $password)
    {
        $encryptedPassword = $this->crypt($password);
        return $encryptedPassword == $encryptedReference;
    }

    public function crypt($password)
    {
        $encrypted = password_hash($password, PASSWORD_BCRYPT);
        return $encrypted;
    }
}