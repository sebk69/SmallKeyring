<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;


use Symfony\Component\Form\Extension\Core\Type\TextType;

class PasswordType extends TextType
{
    /**
     * Return input type
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'password';
    }
}