<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Bundle\SmallKeyringModelBundle\Model;


use phpseclib\Crypt\RSA;
use Sebk\SmallOrmBundle\Dao\Model;

class User extends Model
{
    const KEY_DIR = "users";

    /**
     * Generate new private key
     * @param string $password
     */
    public function generateKeys(string $password = null): void
    {
        // Create key pair
        $rsa = new RSA();
        $rsa->setPassword($password);

        // Create RSA private key
        $RSAKeyPair = $rsa->createKey(512);

        // Save it
        $keyPath = $this->container->getParameter("HOME_DIR") . "/" . static::KEY_DIR;
        if (!is_dir($keyPath)) {
            mkdir($keyPath);
            chmod($keyPath, 0700);
        }
        $privateFile = tempnam($keyPath, "");
        file_put_contents($privateFile, $RSAKeyPair["privatekey"]);
        chmod($privateFile, 0600);

        // Save key
        $this->setPrivateKeyPath($privateFile);
        $this->setHasKeyPassword($password !== null ? 1 : 0);
        $this->persist();
    }

    /**
     * Encrypt message
     * @param string $message
     * @return string
     * @throws \Exception
     */
    public function encode(string $message): string
    {
        // Create rsa object
        $rsa = new RSA();

        // Set passphrase if necessary
        if($this->getHasKeyPassword() == 1) {
            $passphrase = $this->getPassphrase();
            $rsa->setPassword($passphrase);
        }

        // Load private key
        if($this->getPrivateKeyPath() == null) {
            throw new \Exception("Private key not defined");
        }

        $privateKey = file_get_contents($this->getPrivateKeyPath());
        if(!$privateKey) {
            throw new \Exception("File not found");
        }

        $rsa->loadKey($privateKey);

        // Encrypt message
        return $rsa->encrypt($message);
    }

    /**
     * Decode message
     * @param string $encryptedMessage
     * @return string
     * @throws \Exception
     */
    public function decode(string $encryptedMessage): string
    {
        $rsa = new RSA();

        // Set passphrase if necessary
        if($this->getHasKeyPassword() == 1) {
            $passphrase = $this->getPassphrase();
            $rsa->setPassword($passphrase);
        }

        // Load private key
        if($this->getPrivateKeyPath() == null) {
            throw new \Exception("Private key not defined");
        }
        $privateKey = file_get_contents($this->getPrivateKeyPath());
        if(!$privateKey) {
            throw new \Exception("File not found");
        }
        $rsa->loadKey($privateKey);

        if($rsa->modulus == null || $rsa->publicExponent == null) {
            throw new \exception("Broken key");
        }

        // Generate public key
        $publicKey = $rsa->_convertPublicKey($rsa->modulus, $rsa->publicExponent);

        // And load it
        $rsa->loadKey($publicKey);

        // Return decrypted message
        return $rsa->decrypt($encryptedMessage);
    }

    /**
     * Get session passphrase
     * @return null|string
     */
    protected function getPassphrase()
    {
        // Get passphrase stored in session
        /** @var SessionInterface $session */
        $session = $this->container->get("session");
        /** @var Encrypt $encrypt */
        $encrypt = $this->container->get("App\Security\Encrypt");

        $encodedPassphrase = $session->get("passphrase");
        if($encodedPassphrase !== null) {
            $passphrase = $encrypt->decrypt($encodedPassphrase);
        } else {
            $passphrase = "";
        }

        return $passphrase;
    }

    /**
     * Return true if keyPair is defined
     * @return bool
     */
    public function isKeyPairDefined(): bool
    {
        if($this->getPrivateKeyPath() === null || $this->getHasKeyPassword() === null) {
            return false;
        }

        return true;
    }
}