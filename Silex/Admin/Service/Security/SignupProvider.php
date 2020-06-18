<?php

namespace Admin\Service\Security;

use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Gigablah\Silex\OAuth\Security\User\StubUser;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * OAuth in-memory signup user provider.
 */
class SignupProvider implements UserProviderInterface, OAuthUserProviderInterface
{
    private $users;
    private $credentials;

    /**
     * Constructor.
     *
     * @param array $users       An array of users
     * @param array $credentials A map of usernames with service credentials (service name and uid)
     */
    public function __construct(array $users = array(), array $credentials = array())
    {
        foreach ($users as $username => $attributes) {
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $email = isset($attributes['email']) ? $attributes['email'] : null;
            $enabled = isset($attributes['enabled']) ? $attributes['enabled'] : true;
            $roles = isset($attributes['roles']) ? (array) $attributes['roles'] : array();
            $user = new StubUser($username, $password, $email, $roles, $enabled, true, true, true);
            $this->createUser($user);
        }

        $this->credentials = $credentials;
    }

    public function createUser(UserInterface $user)
    {
        $this->users[$user->getEmail()] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (isset($this->users[$username])) {
            $user = $this->users[$username];
        } else {
            return null;
        }

        return new StubUser($user->getUsername(), $user->getPassword(), $user->getEmail(), $user->getRoles(), $user->isEnabled(), $user->isAccountNonExpired(), $user->isCredentialsNonExpired(), $user->isAccountNonLocked());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthCredentials(OAuthTokenInterface $token)
    {
        foreach ($this->credentials as $username => $credentials) {
            foreach ($credentials as $credential) {
                if ($credential['service'] == $token->getService() && $credential['uid'] == $token->getUid()) {
                    return $this->loadUserByUsername($token->getEmail());
                }
            }
        }

        $user = new StubUser($token->getUsername(), '', $token->getEmail(), array('ROLE_SIGNUP'), true, true, true, true);
        $this->createUser($user);

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof StubUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Gigablah\\Silex\\OAuth\\Security\\User\\StubUser';
    }
}
