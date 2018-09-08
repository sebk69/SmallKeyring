<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Controller;

use App\Bundle\SmallKeyringModelBundle\Dao\Key;
use App\Form\ChangePassphraseType;
use App\Form\ExtractUserType;
use App\Form\ProfileType;
use App\Form\RemoveUserType;
use App\Form\UserEnableType;
use App\Security\ChangePassphraseForm;
use App\Security\Encrypt;
use App\Security\ExtractUserForm;
use App\Security\UserEnableForm;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sebk\SmallOrmBundle\Factory\Dao;
use \Sebk\SmallUserBundle\Controller\AbstractUserController;
use Sebk\SmallUserBundle\Model\User;
use Sebk\SmallUserBundle\Security\UserProvider;
use Sebk\SmallUserBundle\Security\UserVoter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class UserController
 * @package App\Controller
 * @route("/home/user")
 */
class UserController extends AbstractUserController
{
    /**
     * Create user form
     * @param User $user
     * @return Form
     */
    protected function createProfileForm(User $user): Form
    {
        $user->setPasswordConfirm("");
        $user->setSave(null);
        $form = $this->createForm(ProfileType::class, $user);

        return $form;
    }

    /**
     * Redirect to home if security failure
     * @return string
     */
    protected function getAuthFailRoute(): string
    {
        return "home";
    }

    /**
     * Edit profile route
     * @return string
     */
    protected function getProfileRoute(): string
    {
        return "user_profile";
    }

    /**
     * Edit profile form
     * @route("/profile", methods={"GET"}, name="user_profile")
     */
    public function getProfileEdit(Request $request)
    {
        $user = $this->getUserModel();
        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\User $userSmallKeyringDao */
        $userSmallKeyringDao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "User");
        $userSmallKeyring = $userSmallKeyringDao->findOneBy(["id" => $user->getId()]);

        // Security
        try {
            $this->denyAccessUnlessGranted(UserVoter::PERSONAL_EDIT, $user);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute($this->getAuthFailRoute());
        }

        // Create forms
        $form = $this->createProfileForm($user);
        $changePassphraseForm = $this->createForm(ChangePassphraseType::class, new ChangePassphraseForm());
        $removeUserForm = $this->createForm(RemoveUserType::class, new \stdClass());
        $extractUserForm = $this->createForm(ExtractUserType::class, new ExtractUserForm());

        // Render
        return $this->render('user/profile.html.twig', [
            "form" => $form->createView(),
            "form_change_passphrase" => $changePassphraseForm->createView(),
            "form_remove_user" => $removeUserForm->createView(),
            "form_extract" => $extractUserForm->createView(),
            "user" => $this->getUser(),
            "hasKeyPassword" => $userSmallKeyring->getHasKeyPassword(),
        ]);
    }

    /**
     * Post edit profile form
     * @route("/profile", methods={"POST"}, name="post_user_profile")
     */
    public function postProfileEdit(Request $request)
    {
        return parent::postProfileEdit($request);
    }

    /**
     * Manage users
     * @route("/list", methods={"GET"}, name="list_users")
     */
    public function getUserList()
    {
        // Only superadmin can use this route
        if(!$this->getUser()->hasRole("SUPER_ADMIN")) {
            return $this->redirectToRoute("home");
        }

        // Get user list
        $users = $this->get("sebk_small_orm_dao")->get("SebkSmallUserBundle", "User")->findBy([]);

        // Set enable form
        foreach($users as $user) {
            $userEnabled = new UserEnableForm();
            $userEnabled->setUserId($user->getId());
            $userEnabled->setEnabled($user->getEnabled());
            $user->setEnableForm($this->createForm(UserEnableType::class, $userEnabled)->createView());
        }

        // Render view
        return $this->render("user/list.html.twig", ["user" => $this->getUser(), "users" => $users]);
    }

    /**
     * Post user form
     * @route("/enable", methods={"POST"}, name="post_enable")
     */
    public function postEnableForm(UserProvider $userProvider, Request $request)
    {
        // Only superadmin can use this route
        if(!$this->getUser()->hasRole("SUPER_ADMIN")) {
            return $this->redirectToRoute("home");
        }

        // Handle request
        $enable = new UserEnableForm();
        $form = $this->createForm(UserEnableType::class, $enable);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {
            // Update user
            $user = $userProvider->loadUserById($enable->getUserId());
            $user->setEnabled($enable->getEnabled());
            $userProvider->updateUser($user);

            // Return to list
            return $this->redirectToRoute("list_users");
        }

        // if not valid, redirect to home
        $this->redirectToRoute("home");
    }

    /**
     * Post remove user form
     * @route("/remove", methods={"POST"}, name="post_remove_account")
     */
    public function postRemoveAccount(Request $request, Dao $daoFactory)
    {
        // Handle request
        $removeUserForm = $this->createForm(RemoveUserType::class, new \stdClass());
        $removeUserForm->handleRequest($request);

        // Check
        if($removeUserForm->isSubmitted() && $removeUserForm->isValid()) {
            // and remove account
            /** @var Key $daoKeys */
            $daoKeys = $daoFactory->get("BundleSmallKeyringModelBundle", "Key");
            foreach ($daoKeys->listKeys($this->getUser()->getId()) as $key) {
                $key->delete();
            }
            /** @var \App\Bundle\SmallKeyringModelBundle\Dao\User $daoUser */
            $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
            $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);
            $user->delete();

            $this->addFlash("notice", "Your account have been removed");
        }

        return $this->redirectToRoute("logout");
    }

    /**
     * Get user data extraction
     * @route("/extract", methods={"POST"}, name="extract_user_data")
     */
    public function extract(Dao $daoFactory, Request $request, SessionInterface $session, UserProvider $userProvider, Encrypt $encrypt)
    {
        // Handle request
        $extractUser = new ExtractUserForm();
        $form = $this->createForm(ExtractUserType::class, $extractUser);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {

            // Check passphrase
            /** @var \App\Bundle\SmallKeyringModelBundle\Dao\User $userSmallKeyringDao */
            $userSmallKeyringDao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "User");
            $userSmallKeyring = $userSmallKeyringDao->findOneBy(["id" => $this->getUser()->getId()]);
            if($userSmallKeyring->getHasKeyPassword() == 1) {
                try {
                    if ($encrypt->decrypt($session->get("passphrase")) != $extractUser->getPassphrase()) {
                        $this->addFlash("error", "Wrong passphrase");
                        return $this->redirectToRoute("user_profile");
                    }
                } catch(\Exception $e) {
                    $this->addFlash("error", "Wrong passphrase");
                    return $this->redirectToRoute("user_profile");
                }
            }

            // Check password
            if(!$userProvider->checkPassword($this->getUser(), $extractUser->getPassword())) {
                $this->addFlash("error", "Wrong password");
                return $this->redirectToRoute("user_profile");
            }

            /** @var Key $keyDao */
            $keyDao = $daoFactory->get("BundleSmallKeyringModelBundle", "Key");

            // Genrate extraction
            $html =
                $this->renderView("user/extract.html.twig", [
                    "user" => $this->getUser(),
                    "keys" => $keyDao->listKeys($this->getUser()->getId()),
                ]);

            return new PdfResponse(
                $this->get("knp_snappy.pdf")->getOutputFromHtml($html),
                "smallKeyring.pdf"
            );
        }

        return $this->redirectToRoute("logout");
    }
}