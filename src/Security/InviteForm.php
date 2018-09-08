<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


class InviteForm
{
    /** @var string */
    protected $email = "";

    /**
     * Get email
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * Set email
     * @param string $email
     * @return InviteForm
     */
    public function setEmail(string $email) : InviteForm
    {
        $this->email = $email;

        return $this;
    }
}