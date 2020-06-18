<?php

namespace Admin\Service\Security;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use OAuth\Common\Token\TokenInterface;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdministratorProvider implements UserProviderInterface, OAuthUserProviderInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $administrators = \AdministratorFactory::factoryByEmail($username);
        $solutionIds = array();
        $administratorIds = array();

        if (count($administrators) === 0) {
            return null;
        } else {
            foreach ($administrators as $administrator) {
                /* @var \Administrator $administrator */
                $solutionIds[] = $administrator->getClientSolutionId();
                $administratorIds[$administrator->getClientSolutionId()] = $administrator->getId();
            }
        }

        return new \Admin\Model\Administrator($administrator->getEmail(), $administrator->getPassword(), $administrator->getEmail(), $solutionIds, $administratorIds, array('ROLE_ADMIN'), $administrator->isActive());
    }

    public function loadUserByUsernameAndSolution($username, $solutionId)
    {
        // TODO: rework this.
        if ($solutionId === null) {
            // login domain
            $administrators = \AdministratorFactory::factoryByEmail($username, true, false);
        } else {
            // solutions's domain
            $administrators = \AdministratorFactory::factoryByEmail($username, false, false, $solutionId);
        }

        if (count($administrators) === 0) {
            return null;
        } else {
            foreach ($administrators as $administrator) {
                /* @var \Administrator $administrator */
                $solutionIds[] = $administrator->getClientSolutionId();
                $administratorIds[$administrator->getClientSolutionId()] = $administrator->getId();
            }
        }

        return new \Admin\Model\Administrator($administrator->getEmail(), $administrator->getPassword(), $administrator->getEmail(), $solutionIds, $administratorIds, array('ROLE_ADMIN'), $administrator->isActive());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthCredentials(OAuthTokenInterface $token)
    {
        // auth using email if available
        $email = $token->getEmail();
        if (!empty($email)) {
            $administrators = \AdministratorFactory::factoryByEmail($email, true);
        } else {
            $administrators = \AdministratorFactory::factoryByOAuth($token->getService(), $token->getUid(), true);
        }

        if (count($administrators) === 0) {
            return null;
        } else {
            foreach ($administrators as $administrator) {
                /* @var \Administrator $administrator */
                $solutionIds[] = $administrator->getClientSolutionId();
                $administratorIds[$administrator->getClientSolutionId()] = $administrator->getId();
            }
        }

        return new \Admin\Model\Administrator($administrator->getEmail(), $administrator->getPassword(), $administrator->getEmail(), $solutionIds, $administratorIds, array('ROLE_ADMIN'), $administrator->isActive());
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \Admin\Model\Administrator) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsernameAndSolution($user->getUsername(), $user->getSolutionId());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Admin\\Model\\Administrator';
    }
}