<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;

use App\Security\InviteForm;
use App\Security\LostPassword;
use App\Security\LostPasswordForm;
use App\Security\UserEnableForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEnableType extends AbstractType
{
    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("userId", HiddenType::class, [])
            ->add("enabled", ChoiceType::class, [
                "choices" => [
                    "Enabled" => 1,
                    "Disabled" => 0,
                ],
                "attr" => ["class" => "custom-select enable-select"]
            ])
            ->add("submit", SubmitType::class, ["attr" => ["class" => "invisible"]])
        ;
    }

    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => UserEnableForm::class,
        ]);
    }

}