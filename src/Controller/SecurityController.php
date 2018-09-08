<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Controller;


use App\Bundle\SmallKeyringModelBundle\Dao\Key;
use App\Form\ChangePassphraseType;
use App\Security\ChangePassphraseForm;
use App\Form\AskPassphraseType;
use App\Form\InviteType;
use App\Form\LostPasswordType;
use App\Form\NewPassphraseType;
use App\Form\ResetPasswordType;
use App\Form\SignUpType;
use App\Security\AskPassphraseForm;
use App\Security\Encrypt;
use App\Security\Invite;
use App\Security\InviteForm;
use App\Security\LostPassword;
use App\Security\LostPasswordForm;
use App\Security\NewPassphraseForm;
use App\Security\ResetPasswordForm;
use App\Security\SignUpForm;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallUserBundle\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 * @route("/")
 */
class SecurityController extends Controller
{
    /**
     * Display login form
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @route("login", methods={"GET"}, name="login")
     */
    public function getLogin(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            "last_username" => $lastUsername,
            "error"         => $error,
        ]);
    }

    /**
     * Check login
     * @throws \Exception
     * @route("/home/login_check", methods={"POST"}, name="login_check")
     */
    public function loginCheck()
    {
        throw new \Exception("Security breach");
    }

    /**
     * Logout
     * @throws \Exception
     * @route("/home/logout", methods={"GET"}, name="logout")
     */
    public function logout()
    {
        throw new \Exception("Security breach");
    }

    /**
     * @route("/home/invite", methods={"GET"}, name="invite")
     */
    public function inviteForm()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Create form
        $invite = new InviteForm();
        $form = $this->createForm(InviteType::class, $invite);

        // Render
        return $this->render('security/invite.form.html.twig', [
            "form" => $form->createView(),
            "user" => $this->getUser(),
        ]);
    }

    /**
     * @route("/home/invite", methods={"POST"}, name="post_invite")
     */
    public function postInvite(Invite $inviteService, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Handle request form
        /** @var Key $key */
        $invitation = new InviteForm();
        $form = $this->createForm(InviteType::class, $invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $inviteService->sendInvitation($this->getUser(), $invitation->getEmail());
            } catch(\exception $e) {
                $this->addFlash("error", $e->getMessage());
                return $this->redirectToRoute("invite");
            }
        }

        $this->addFlash("notice", "The invitation has been sent");

        return $this->redirectToRoute("invite");
    }

    /**
     * @route("/signup", methods={"GET"}, name="signup_form")
     */
    public function signup(Invite $inviteService, UserProvider $userProvider, Request $request)
    {
        // Get token data
        try {
            $tokenData = $inviteService->getTokenData($request->get("token"));
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        // Check if user exists
        try {
            $userProvider->loadUserByUsername($tokenData["user"]->getEmail());
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        // Check guest not already registered
        $registered = true;
        try {
            $userProvider->loadUserByUsername($tokenData["destEmail"]);
        } catch (UsernameNotFoundException $e) {
            $registered = false;
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        if ($registered) {
            $this->addFlash("error", "You have already been registered");

            return $this->redirectToRoute("logout");
        }

        // Check validity date
        $now = new \DateTime();
        if($tokenData["limitDate"]->format("U") < $now->format("U")) {
            $this->addFlash("error", "Limit date expired");

            return $this->redirectToRoute("logout");
        }

        // Create form
        $signUp = new SignUpForm();
        $form = $this->createForm(SignUpType::class, $signUp);

        // Render
        return $this->render('security/signup.html.twig', [
            "form" => $form->createView(),
            "email" => $tokenData["destEmail"],
        ]);
    }

    /**
     * @route("/signup", methods={"POST"}, name="post_signup_form")
     */
    public function postSignup(Invite $inviteService, UserProvider $userProvider, Request $request)
    {
        // Get token data
        try {
            $tokenData = $inviteService->getTokenData($request->get("token"));
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        // Check if user exists
        try {
            $userProvider->loadUserByUsername($tokenData["user"]->getEmail());
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        // Check guest not already registered
        $registered = true;
        try {
            $userProvider->loadUserByUsername($tokenData["destEmail"]);
        } catch (UsernameNotFoundException $e) {
            $registered = false;
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }

        if ($registered) {
            $this->addFlash("error", "You have already been registered");

            return $this->redirectToRoute("logout");
        }

        // Check validity date
        $now = new \DateTime();
        if($tokenData["limitDate"]->format("U") < $now->format("U")) {
            $this->addFlash("error", "Limit date expired");

            return $this->redirectToRoute("logout");
        }

        // handle request
        $signUp = new SignUpForm();
        $form = $this->createForm(SignUpType::class, $signUp);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($signUp->getPassword() != $signUp->getPasswordConfirm()) {
                $this->addFlash("error", "Password and confirmation don't match");

                return $this->render('security/signup.html.twig', [
                    "form" => $form->createView(),
                    "email" => $tokenData["destEmail"],
                ]);
            }

            $userProvider->createUser($tokenData["destEmail"], $signUp->getNickname(), $signUp->getPassword());

            $this->addFlash("notice", "Registration done");
            return $this->redirectToRoute("logout");
        }
    }

    /**
     * @route("/lostPassword", methods={"GET"}, name="lost_password_form")
     */
    public function lostPasswordForm()
    {
        // Create form
        $lostPassword = new LostPasswordForm();
        $form = $this->createForm(LostPasswordType::class, $lostPassword);

        // Render
        return $this->render('security/lost.password.form.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @route("/lostPassword", methods={"POST"}, name="post_lost_password_form")
     */
    public function postLostPasswordForm(UserProvider $userProvider, LostPassword $lostPasswordService, Request $request)
    {
        $lostPassword = new LostPasswordForm();
        $form = $this->createForm(LostPasswordType::class, $lostPassword);

        $form->handleRequest($request);

        try {
            $user = $userProvider->loadUserByUsername($lostPassword->getEmail());
        } catch (UsernameNotFoundException $e) {
            $this->addFlash("error", "No user have this email");
            return $this->redirectToRoute("lost_password_form");
        } catch (\Exception $e) {
            $this->addFlash("error", "Something is broken, please try again later");
            return $this->redirectToRoute("lost_password_form");
        }

        $lostPasswordService->sendLostPassword($user);
        $this->addFlash("notice", "An email has been send to you. Click on link inside to reset password.");

        return $this->redirectToRoute("logout");
    }

    /**
     * @route("/resetPassword", methods={"GET"}, name="reset_password_form")
     */
    public function resetPasswordForm(UserProvider $userProvider, Encrypt $encrypt, Request $request)
    {
        // Get token data
        try {
            $tokenData = $encrypt->decodeToken($request->get("token"));
            $tokenUser = $userProvider->loadUserById($tokenData[0]);
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }
        $tokenEmail = $tokenData[1];

        // Check email
        if($tokenUser->getEmail() != $tokenEmail) {
            return $this->redirectToRoute("logout");
        }

        // Check date limit
        $dateLimit = \DateTime::createFromFormat("Y-m-d H:i:s", $tokenData[2]);
        if($dateLimit->format("U") < date("U")) {
            $this->addFlash("error", "This link has expired");
            return $this->redirectToRoute("logout");
        }

        // Create form
        $resetPassword = new ResetPasswordForm();
        $resetPassword->setEmail($tokenEmail);
        $form = $this->createForm(ResetPasswordType::class, $resetPassword);

        // Render
        return $this->render('security/reset.password.form.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @route("/resetPassword", methods={"POST"}, name="post_reset_password")
     */
    public function postResetPassword(UserProvider $userProvider, Encrypt $encrypt, Request $request)
    {
        // Get token data
        try {
            $tokenData = $encrypt->decodeToken($request->get("token"));
            $tokenUser = $userProvider->loadUserById($tokenData[0]);
        } catch (\Exception $e) {
            return $this->redirectToRoute("logout");
        }
        $tokenEmail = $tokenData[1];

        // Check email
        if($tokenUser->getEmail() != $tokenEmail) {
            return $this->redirectToRoute("logout");
        }

        // Check date limit
        $dateLimit = \DateTime::createFromFormat("Y-m-d H:i:s", $tokenData[2]);
        if($dateLimit->format("U") < date("U")) {
            $this->addFlash("error", "This link has expired");
            return $this->redirectToRoute("logout");
        }

        // handle request
        $resetPassword = new ResetPasswordForm();
        $form = $this->createForm(ResetPasswordType::class, $resetPassword);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($resetPassword->getPassword() != $resetPassword->getPasswordConfirm()) {
                $this->addFlash("error", "Password and confirmation don't match");
                return $this->redirectToRoute("reset_password_form");
            }

            if($tokenUser->getEmail() != $resetPassword->getEmail()) {
                return $this->redirectToRoute("logout");
            }

            $userProvider->updateUser($tokenUser, $resetPassword->getPassword());

            $this->addFlash("notice", "Password changed done");
            return $this->redirectToRoute("logout");
        }
    }

    /**
     * @route("/home/createUserKeys", methods={"POST"}, name="create_user_key_pair")
     */
    public function createUserKeyPair(Encrypt $encrypt, Dao $daoFactory, SessionInterface $session, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // handle request
        $newPassphrase = new NewPassphraseForm();
        $form = $this->createForm(NewPassphraseType::class, $newPassphrase);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Passphrase don't match
            if($newPassphrase->getPassphrase() != $newPassphrase->getPassphraseConfirm()) {
                $this->addFlash("error", "Passphrase and confirmation don't match");
                return $this->redirectToRoute("new_passphrase_form");
            }

            // Load user model
            /** @var User $daoUser */
            $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
            /** @var \App\Bundle\SmallKeyringModelBundle\Model\User $user */
            $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);
            if($user->isKeyPairDefined()) {
                throw new \Exception("TODO: Change key pair");
            }

            // Generate passphrase
            if($newPassphrase->getPassphrase() != "") {
                $session->set("passphrase", $encrypt->encrypt($newPassphrase->getPassphrase()));
                $user->generateKeys($newPassphrase->getPassphrase());
            } else {
                $user->generateKeys();
            }
        }

        return $this->redirectToRoute("key_list");
    }

    /**
     * @route("/newPassphrase", methods={"GET"}, name="new_passphrase_form")
     */
    public function newPassphrase()
    {
        // Create form
        $newPassphrase = new NewPassphraseForm();
        $form = $this->createForm(NewPassphraseType::class, $newPassphrase);

        // Render
        return $this->render('security/new.passphrase.form.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @route("/home/askPassphrase", methods={"GET"}, name="ask_passphrase_form")
     */
    public function askPassphrase(Dao $daoFactory)
    {
        // Load user model
        /** @var User $daoUser */
        $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
        /** @var \App\Bundle\SmallKeyringModelBundle\Model\User $user */
        $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);

        try {
            $valid = $user->decode($user->encode("validation")) == "validation";
        } catch (\Exception $e) {
            $valid = false;
        }

        if(!$valid) {
            // Create form
            $askPassphrase = new AskPassphraseForm();
            $form = $this->createForm(AskPassphraseType::class, $askPassphrase);

            // Render
            return $this->render('security/ask.passphrase.html.twig', [
                "form" => $form->createView(),
            ]);
        } else {
            // session passphrase is valid, redirect to key list
            return $this->redirectToRoute("key_list");
        }
    }

    /**
     * @route("/home/askPassphrase", methods={"POST"}, name="post_ask_passphrase_form")
     */
    public function postAskPassphrase(Encrypt $encrypt, SessionInterface $session, Dao $daoFactory, Request $request)
    {
        $askPassphrase = new AskPassphraseForm();
        $form = $this->createForm(AskPassphraseType::class, $askPassphrase);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($askPassphrase->getPassphrase() == "") {
                $this->addFlash("error", "You must enter your passphrase");
                return $this->redirectToRoute("ask_passphrase_form");
            }

            // Load user model
            /** @var User $daoUser */
            $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
            /** @var \App\Bundle\SmallKeyringModelBundle\Model\User $user */
            $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);

            // Save passphrase in session (crypted)
            $session->set("passphrase", $encrypt->encrypt($askPassphrase->getPassphrase()));

            // Try passphrase
            try {
                if($user->decode($user->encode("validation")) != "validation") {
                    $this->addFlash("error", "Wrong passphrase");
                    return $this->redirectToRoute("ask_passphrase_form");
                }
            } catch (\Exception $e) {
                $this->addFlash("error", "Wrong passphrase");
                return $this->redirectToRoute("ask_passphrase_form");
            }

            return $this->redirectToRoute("key_list");
        }

        return $this->redirectToRoute("logout");
    }

    /**
     * Post change passphrase
     * @route("/home/changePassphrase", methods={"POST"}, name="post_change_passphrase")
     */
    public function postChangePassphrase(Dao $daoFactory, SessionInterface $session, Encrypt $encrypt, Request $request)
    {
        // Handle request
        $changePassphrase = new ChangePassphraseForm();
        $form = $this->createForm(ChangePassphraseType::class, $changePassphrase);
        $form->handleRequest($request);

        // Check form
        if($form->isValid() && $form->isSubmitted()) {
            // Check old passphrase
            if(empty($session->get("passphrase")) && !empty($changePassphrase->getOldPassphrase())) {
                $this->addFlash("error", "Wrong old passphrase");
                return $this->redirectToRoute("user_profile");
            }

            $passphraseDecoded = $encrypt->decrypt($session->get("passphrase"));

            if($passphraseDecoded != $changePassphrase->getOldPassphrase()) {
                $this->addFlash("error", "Wrong old passphrase");
                return $this->redirectToRoute("user_profile");
            }

            // Check confirmation
            if($changePassphrase->getPassphrase() != $changePassphrase->getPassphraseConfirmation()) {
                $this->addFlash("error", "Passphrase and confirmation don't match");
                return $this->redirectToRoute("user_profile");
            }

            // Change passphrase
            /** @var Key $keyDao */
            $keyDao = $daoFactory->get("BundleSmallKeyringModelBundle", "Key");
            $keyDao->getConnection()->startTransaction();
            $keys = $keyDao->listKeys($this->getUser()->getId());

            // Load user model
            /** @var User $daoUser */
            $daoUser = $daoFactory->get("BundleSmallKeyringModelBundle", "User");
            /** @var \App\Bundle\SmallKeyringModelBundle\Model\User $user */
            $user = $daoUser->findOneBy(["id" => $this->getUser()->getId()]);

            // Generate passphrase
            if($changePassphrase->getPassphrase() != "") {
                $session->set("passphrase", $encrypt->encrypt($changePassphrase->getPassphrase()));
                $user->generateKeys($changePassphrase->getPassphrase());
            } else {
                $session->set("passphrase", null);
                $user->generateKeys();
            }
            foreach ($keys as $key) {
                $key->persist();
            }
            $keyDao->getConnection()->commit();

            $this->addFlash("notice", "Passphrase have been changed and keys have been reencrypted");
            return $this->redirectToRoute("user_profile");
        }

        return $this->redirectToRoute("logout");
    }

}