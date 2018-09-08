<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


class ResetPasswordForm
{
    protected $email;
    protected $password;
    protected $passwordConfirm;

    /**
     * Get email
     * @return null|string
     */
    public function getEmail() : ?string
    {
        return $this->email;
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
     * Set email
     * @param $email
     * @return SignUpForm
     */
    public function setEmail($email) : ResetPasswordForm
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set password
     * @param $password
     * @return SignUpForm
     */
    public function setPassword($password): ResetPasswordForm
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set password confirmation
     * @param $passwordConfirm
     * @return SignUpForm
     */
    public function setPasswordConfirm($passwordConfirm): ResetPasswordForm
    {
        $this->passwordConfirm = $passwordConfirm;

        return $this;
    }
}