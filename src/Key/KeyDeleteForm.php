<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Key;

/**
 * Class KeyDeleteForm
 * @package App\Key
 */
class KeyDeleteForm
{
    protected $idKey;


    /**
     * Get id of key to delete
     * @return int|null
     */
    public function getIdKey(): ?int
    {
        return $this->idKey;
    }

    /**
     * Set id of key to delete
     * @param int $idKey
     * @return KeyDeleteForm
     */
    public function setIdKey(int $idKey): KeyDeleteForm
    {
        $this->idKey = $idKey;

        return $this;
    }
}