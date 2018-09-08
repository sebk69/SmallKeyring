<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Bundle\SmallKeyringModelBundle\Validator;


use Sebk\SmallOrmBundle\Dao\DaoEmptyException;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallOrmBundle\Validator\AbstractValidator;

class Key extends AbstractValidator
{
    /**
     * Validate a key
     * @return bool
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function validate()
    {
        $result = true;

        /** @var \App\Bundle\SmallKeyringModelBundle\Model\Key $key */
        $key = $this->model;
        /** @var Dao $daoFactory */
        $daoFactory = $this->daoFactory;

        // User must exists
        try {
            $user = $daoFactory->get("SebkSmallUserBundle", "User")->findOneBy(["id" => $key->getUserId()]);
        } catch (DaoEmptyException $e) {
            $this->message .= "The user not exists";
            $result = false;
        }

        // Tag must not be empty
        if(!$this->testNonEmpty("tag")) {
            $this->message .= "The tag is mandatory";
            $result = false;
        }

        // Tag must be unique by user
        if(!$this->testUniqueWithDeterminant("userId", $this->model->getUserId(), "tag")) {
            $this->message .= "The tag must be unique";
            $result = false;
        }

        return $result;
    }
}