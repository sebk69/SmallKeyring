<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;
use Sebk\SmallUserBundle\Security\User;
use Sebk\SmallUserBundle\Security\UserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class Invite
 * Class Invite
 * @package App\Security
 */
class LostPassword
{
    protected $mailer;
    protected $encrypt;
    protected $userProvider;
    protected $twig;

    /**
     * LostPassword constructor.
     * @param \Swift_Mailer $mailer
     * @param Encrypt $encrypt
     * @param UserProvider $userProvider
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, Encrypt $encrypt, UserProvider $userProvider, \Twig_Environment $twig) {
        $this->mailer = $mailer;
        $this->encrypt = $encrypt;
        $this->userProvider = $userProvider;
        $this->twig = $twig;
    }

    /**
     * Return token for lost password
     * @param User $fromUser
     * @param string $destEmail
     * @return string
     * @throws \Exception
     */
    protected function generateToken(User $user): string
    {
        // Create token data
        $data = [
                $user->getId(),
                $user->getEmail(),
                date('Y-m-d H:i:s', strtotime("+1 day")),
        ];

        // Return token
        return $this->encrypt->encodeToken($data);
    }

    /**
     * Send invitation
     * @param User $fromUser
     * @param string $destEmail
     * @return $this
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendLostPassword(User $user)
    {
        $message = (new \Swift_Message("Password reset"))
            ->setFrom("no-reply@small-keyring.com")
            ->setTo($user->getEmail())
            ->setBody($this->twig->render("security/lost.password.email.twig", [
                "token" => $this->generateToken($user),
            ]), "text/html");
        if(!$this->mailer->send($message)) {
            throw new \Exception("Fail to send email");
        }

        return $this;
    }
}