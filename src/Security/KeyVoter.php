<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Security;


use App\Bundle\SmallKeyringModelBundle\Model\Key;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class KeyVoter extends Voter
{
    const CREATE = "create";
    const EDIT = "edit";

    /**
     * Check if vote supported
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if(!in_array($attribute,  [static::CREATE, static::EDIT])) {
            return false;
        }

        if(!$subject instanceof Key) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch($attribute) {
            case static::EDIT:
                if ($token->getUser()->getId() == $subject->getUserId()) {
                    return true;
                }
                break;

            case static::CREATE:
                return true;
                break;

            default:
                throw new \LogicException("Security failure");
        }

        return false;
    }
}