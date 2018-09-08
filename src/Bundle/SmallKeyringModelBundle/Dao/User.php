<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Bundle\SmallKeyringModelBundle\Dao;

class User extends \Sebk\SmallUserBundle\Dao\User
{
    public function build()
    {
        parent::build();
        $this->addField("private", "privateKeyPath");
        $this->addField("has_key_password", "hasKeyPassword");
    }
}