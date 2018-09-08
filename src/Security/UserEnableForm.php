<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */


namespace App\Security;

/**
 * Class UserEnableForm
 * @package App\Security
 */
class UserEnableForm
{
    /** @var int */
    public $userId;
    /** @var int */
    public $enabled;

    /**
     * Return user id
     * @return int|null
     */
    public function getUserId() : ?int
    {
        return $this->userId;
    }

    /**
     * Get enabled
     * @return int|null
     */
    public function getEnabled() : ?int
    {
        return $this->enabled;
    }

    /**
     * Set user id
     * @param int $userId
     * @return UserEnableForm
     */
    public function setUserId(int $userId): UserEnableForm
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Set enabled
     * @param int $enabled
     * @return UserEnableForm
     * @throws \Exception
     */
    public function setEnabled(int $enabled): UserEnableForm
    {
        if(!in_array($enabled, [0, 1])) {
            throw new \Exception("Enabled must be 0 or 1");
        }

        $this->enabled = $enabled;

        return $this;
    }

}