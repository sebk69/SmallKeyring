<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Controller;


use App\Bundle\SmallKeyringModelBundle\Dao\User;
use Sebk\SmallOrmBundle\Factory\Dao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 * @route("/")
 */
class HomeController extends Controller
{
    /**
     * @route("", methods={"GET"}, name="base")
     */
    public function base()
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @route("/home", methods={"GET"}, name="home")
     */
    public function getHome(Dao $daoFactory, Request $request)
    {
        try {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute("logout");
        }

        /** @var User $daoUser */
        $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
        /** @var \App\Bundle\SmallKeyringModelBundle\Model\User $user */
        $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);
        if(!$user->isKeyPairDefined()) {
            return $this->redirectToRoute("new_passphrase_form");
        }

        if($user->getHasKeyPassword() == 1) {
            return $this->redirectToRoute("ask_passphrase_form");
        }

        return $this->redirectToRoute("key_list");
    }
}