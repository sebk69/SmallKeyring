<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;

use App\Security\AskPassphraseForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AskPassphraseType extends AbstractType
{
    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("passphrase", PasswordType::class, ["required" => true, "attr" => ["placeholder" => "Passphrase", "class" => "form-control"]])
            ->add("enter", SubmitType::class, ["attr" => ["class" => "form-control btn btn-success fas fa-check-circle"]])
        ;
    }

    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => AskPassphraseForm::class,
        ]);
    }

}