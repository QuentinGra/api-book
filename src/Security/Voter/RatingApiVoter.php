<?php

namespace App\Security\Voter;

use App\Entity\Rating;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RatingApiVoter extends Voter
{
    public const OWNER = 'RATING_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::OWNER])
            && $subject instanceof Rating;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof Rating) {
            if ($subject->getUser() === $user || in_array('ROLE_ADMIN', $user->getRoles())) {
                return true;
            }
        } else {
            if ($subject->getRating()->getUser() === $user || in_array('ROLE_ADMIN', $user->getRoles())) {
                return true;
            }
        }

        return false;
    }
}
