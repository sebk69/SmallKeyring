<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;



class ExtractUserForm
{
    protected $passphrase;
    protected $password;

    /**
     * Return passphrase
     * @return null|string
     */
    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    /**
     * Get password
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set passphrase
     * @param string $passphrase
     * @return ExtractUserForm
     */
    public function setPassphrase(string $passphrase): ExtractUserForm
    {
        $this->passphrase = $passphrase;

        return $this;
    }

    /**
     * Set password
     * @param string $password
     * @return ExtractUserForm
     */
    public function setPassword(string $password): ExtractUserForm
    {
        $this->password = $password;

        return $this;
    }
}