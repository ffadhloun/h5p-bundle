<?php

namespace Studit\H5PBundle\Core;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class H5PUtils
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Fetch the current user if not present return the anonymous user
     * @return string|UserInterface|null
     */
    protected function getCurrentOrAnonymousUser()
    {
        $token = $this->tokenStorage->getToken();

        if ($token && $token->getUser() !== 'anon.') {
            return $token->getUser();
        }
        return 'anon.';
    }

    /**
     * Fetch current User ID
     * @param UserInterface|null $user
     * @return string|null|integer
     */
    public function getUserId(?UserInterface $user)
    {
        if ($user !== null) {
            if (method_exists($user, 'getId')) {
                return $user->getId();
            }
            if (method_exists($user, 'getUserId')) {
                return $user->getUserId();
            }
            return $user->getUserIdentifier();
        } else {
            return null;
        }
    }
}
