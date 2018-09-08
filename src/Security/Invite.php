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
class Invite
{
    protected $mailer;
    protected $encrypt;
    protected $userProvider;
    protected $twig;

    /**
     * Invite constructor.
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
     * Return token for invite url
     * @param User $fromUser
     * @param string $destEmail
     * @return string
     * @throws \Exception
     */
    protected function generateToken(User $fromUser, string $destEmail): string
    {
        // Throw execption if dest user found
        $notFound = false;
        try {
            $this->userProvider->loadUserByUsername($destEmail);
        } catch (UsernameNotFoundException $e) {
            $notFound = true;
        }

        if(!$notFound) {
            throw new \Exception("This user is already registered");
        }

        // Create token data
        $data = [
                $fromUser->getId(),
                $destEmail,
                date('Y-m-d H:i:s', strtotime("+7 day")),
        ];

        // Return token
        return $this->encrypt->encodeToken($data);
    }

    /**
     * Get data from token
     * @param string $token
     * @return array
     */
    public function getTokenData(string $token): array
    {
        $data = $this->encrypt->decodeToken($token);

        $result = [];

        $result["user"] = $this->userProvider->loadUserById($data[0]);
        $result["destEmail"] = $data[1];
        $result["limitDate"] = \DateTime::createFromFormat("Y-m-d H:i:s", $data[2]);

        return $result;
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
    public function sendInvitation(User $fromUser, string $destEmail)
    {
        $message = (new \Swift_Message("You have been invited"))
            ->setFrom($fromUser->getEmail())
            ->setTo($destEmail)
            ->setBody($this->twig->render("security/invitation.email.twig", [
                "user" => $fromUser,
                "token" => $this->generateToken($fromUser, $destEmail),
            ]), "text/html");
        if(!$this->mailer->send($message)) {
            throw new \Exception("Fail to send email");
        }

        return $this;
    }
}