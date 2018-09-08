<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;

/**
 * Class Encrypt
 * @package App\Security
 */
class Encrypt
{
    const PRIVATE_KEY = "secret";
    const PUBLIC_KEY = "secret.pub";

    protected $homeDir;
    protected $rsaPublic;
    protected $rsaPrivate;

    /**
     * Encrypt constructor.
     * @param $homeDir
     */
    public function __construct($homeDir)
    {
        $this->homeDir = $homeDir;

        if(!file_exists($homeDir."/".static::PRIVATE_KEY) || !file_exists($homeDir."/".static::PUBLIC_KEY)) {
            $keyPair = (new \phpseclib\Crypt\RSA())->createKey(512);
            file_put_contents($homeDir."/".static::PRIVATE_KEY, $keyPair["privatekey"]);
            file_put_contents($homeDir."/".static::PUBLIC_KEY, $keyPair["publickey"]);
        }

        $this->rsaPrivate = new \phpseclib\Crypt\RSA();
        $this->rsaPrivate->loadKey(file_get_contents($homeDir."/".static::PRIVATE_KEY));
        $this->rsaPublic = new \phpseclib\Crypt\RSA();
        $this->rsaPublic->loadKey(file_get_contents($homeDir."/".static::PUBLIC_KEY));
    }

    /**
     * Encrypt a message
     * @param $message
     * @return mixed
     */
    public function encrypt($message)
    {
        return $this->rsaPublic->encrypt($message);
    }

    /**
     * Decrypt a message
     * @param $message
     * @return string
     */
    public function decrypt($message)
    {
        return $this->rsaPrivate->decrypt($message);
    }

    /**
     * Encode a token
     * @param $message
     * @return string
     */
    public function encodeToken($message): string
    {
        $encrypted = $this->encrypt(json_encode($message));
        return rawurlencode(base64_encode($encrypted));
    }

    /**
     * Decode a token
     * @param string $token
     * @return mixed
     */
    public function decodeToken(string $token)
    {
        $message = base64_decode(rawurldecode($token));
        return json_decode($this->decrypt($message));
    }
}