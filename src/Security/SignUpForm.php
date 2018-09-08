<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


class SignUpForm
{
    protected $nickname;
    protected $password;
    protected $passwordConfirm;

    /**
     * Get nickname
     * @return string
     */
    public function getNickname() : ?string
    {
        return $this->nickname;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword() : ?string
    {
        return $this->password;
    }

    /**
     * Get password confirmation
     * @return string
     */
    public function getPasswordConfirm() : ?string
    {
        return $this->passwordConfirm;
    }

    /**
     * Set nickname
     * @param $nickname
     * @return SignUpForm
     */
    public function setNickname($nickname): SignUpForm
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Set password
     * @param $password
     * @return SignUpForm
     */
    public function setPassword($password): SignUpForm
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set password confirmation
     * @param $passwordConfirm
     * @return SignUpForm
     */
    public function setPasswordConfirm($passwordConfirm): SignUpForm
    {
        $this->passwordConfirm = $passwordConfirm;

        return $this;
    }
}