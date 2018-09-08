<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Form;


use App\Bundle\SmallKeyringModelBundle\Model\Key;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class KeyType
 * @package App\Form
 */
class KeyType extends AbstractType
{
    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("tag", TextType::class, ["required" => true, "attr" => ["class" => "form-control"]])
            ->add("username", TextType::class, ["required" => false, "attr" => ["class" => "form-control", "autocomplete" => "off"]])
            ->add("password", PasswordType::class, ["required" => false, "attr" => ["class" => "form-control", "autocomplete" => "off"]])
            ->add("url", TextType::class, ["required" => false, "attr" => ["class" => "form-control"]])
            ->add("command", TextareaType::class, ["required" => false, "attr" => ["class" => "form-control"]])
            ->add("comment", TextareaType::class, ["required" => false, "attr" => ["class" => "form-control"]])
            ->add("save", SubmitType::class, ["attr" => ["class" => "form-control btn btn-success fas fa-check-circle"]])
        ;
    }

    /**
     * Configure options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Key::class,
        ]);
    }
}