<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class CommonController extends Controller
{
    public function vote($object)
    {
        $trustResolver = new AuthenticationTrustResolver(AnonymousToken::class, RememberMeToken::class);
        $authenticatedVoter = new AuthenticatedVoter($trustResolver);
        $vote = $authenticatedVoter->vote($this->get('security.token_storage')->getToken(), $object, array('IS_AUTHENTICATED_FULLY'));

        return $vote;
    }
}