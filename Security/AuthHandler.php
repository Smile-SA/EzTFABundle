<?php

namespace Smile\EzTFABundle\Security;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzTFABundle\Entity\TFA;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use eZ\Publish\Core\MVC\Symfony\Security\User;

/**
 * Class AuthHandler
 * @package Smile\EzTFABundle\Security
 */
class AuthHandler implements ProviderInterface
{
    /** @var ProviderInterface[] $providers */
    private $providers = array();

    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    protected $tfaRepository;

    public function __construct(
        TokenStorage $tokenStorage,
        Registry $doctrineRegistry
    ) {
        $this->tokenStorage = $tokenStorage;

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $entityManager->getRepository('SmileEzTFABundle:TFA');
    }

    /**
     * @param ProviderInterface $provider
     * @param $alias
     */
    public function addProvider(ProviderInterface $provider, $alias)
    {
        $this->providers[$alias] = $provider;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isAuthenticated()
    {
        $providerAlias = $this->getProviderAlias();
        if (!isset($this->providers[$providerAlias]))
            return true;

        return $this->providers[$providerAlias]->isAuthenticated();
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function requestAuthCode(Request $request)
    {
        $providerAlias = $this->getProviderAlias();
        if (!isset($this->providers[$providerAlias]))
            return false;

        return $this->providers[$providerAlias]->requestAuthCode($request);
    }

    protected function getProviderAlias()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!($user instanceof User))
            return false;
        $apiUser = $user->getAPIUser();


        /** @var TFA $userProvider */
        $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);

        return ($userProvider) ? $userProvider->getProvider() : false;
    }

    public function register(TFARepository $tfaRepository, $userId, $provider)
    {
        return null;
    }

    public function getIdentifier()
    {
        return null;
    }

    public function getName()
    {
        return null;
    }

    public function getDescription()
    {
        return null;
    }


}
