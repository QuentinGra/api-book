<?php

namespace App\Event;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();

        /**
         * @var User $user
         */
        $user = $event->getUser();

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'birthDate' => $user->getBirthDate(),
            'roles' => $user->getRoles(),
        ];

        $event->setData($data);
    }
}
