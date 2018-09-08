<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;

use App\Security\InviteForm;
use App\Security\NewPassphraseForm;
use App\Security\ResetPasswordForm;
use App\Security\SignUpForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPassphraseType extends AbstractType
{
    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("passphrase", PasswordType::class, ["required" => false, "attr" => ["placeholder" => "Passphrase", "class" => "form-control"]])
            ->add("passphraseConfirm", PasswordType::class, ["required" => false, "attr" => ["placeholder" => "Confirm passphrase", "class" => "form-control"]])
            ->add("confirm", SubmitType::class, ["attr" => ["class" => "form-control btn btn-success fas fa-check-circle"]])
        ;
    }

    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => NewPassphraseForm::class,
        ]);
    }

}