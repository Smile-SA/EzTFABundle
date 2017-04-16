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

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    /**
     * AuthHandler constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param Registry $doctrineRegistry
     */
    public function __construct(
        TokenStorage $tokenStorage,
        Registry $doctrineRegistry
    ) {
        $this->tokenStorage = $tokenStorage;

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $entityManager->getRepository('SmileEzTFABundle:TFA');
    }

    /**
     * Register new TFA Provider
     *
     * @param ProviderInterface $provider
     * @param string $alias TFA Provider identifier
     */
    public function addProvider(ProviderInterface $provider, $alias)
    {
        $this->providers[$alias] = $provider;
    }

    /**
     * List all TFA Providers
     *
     * @return ProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Check if user is TFA authenticated
     *
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
     * Ask for TFA Authentication
     *
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

    /**
     * Return user TFA Provider if user has activate a TFA Provider
     *
     * @return bool|string
     */
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

    /**
     * Register for current user TFA Provider activated
     *
     * @param TFARepository $tfaRepository
     * @param $userId
     * @param $provider
     * @return null
     */
    public function register(TFARepository $tfaRepository, $userId, $provider)
    {
        return null;
    }

    /**
     * Return TFA Provider identifier
     *
     * @return null
     */
    public function getIdentifier()
    {
        return null;
    }

    /**
     * Return TFA Provider name
     *
     * @return null
     */
    public function getName()
    {
        return null;
    }

    /**
     * Return TFA Provider description
     *
     * @return null
     */
    public function getDescription()
    {
        return null;
    }


}
