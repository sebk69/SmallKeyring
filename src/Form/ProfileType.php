<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;

use Sebk\SmallUserBundle\Form\ProfileType as ParentProfileType;

class ProfileType extends ParentProfileType
{
    protected function getAttributes($field)
    {
        switch($field) {
            case "email":
                return [
                    "class" => "form-control",
                    "placeholder" => "Email",
                ];
            case "nickname":
                return [
                    "class" => "form-control",
                    "placeholder" => "Nickname",
                ];
            case "password":
                return [
                    "class" => "form-control",
                    "placeholder" => "Password",
                ];
            case "passwordConfirm":
                return [
                    "class" => "form-control",
                    "placeholder" => "Confirm password",
                ];
            case "save":
                return [
                    "class" => "form-control btn btn-success",
                ];
            default:
                return [];
        }
    }
}