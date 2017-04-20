<?php

namespace Smile\EzTFABundle\Security;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzTFABundle\Entity\TFA;
use Smile\EzTFABundle\Provider\ProviderAbstract;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Translation\Translator;

/**
 * Class AuthHandler
 * @package Smile\EzTFABundle\Security
 */
class AuthHandler extends ProviderAbstract implements ProviderInterface
{
    /** @var ProviderInterface[] $providers */
    private $providers = array();

    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    protected $providersConfig;

    /**
     * AuthHandler constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param Registry $doctrineRegistry
     */
    public function __construct(
        Session $session,
        Translator $translator,
        TokenStorage $tokenStorage,
        Registry $doctrineRegistry,
        $providersConfig
    ) {
        parent::__construct($session, $translator);
        $this->tokenStorage = $tokenStorage;

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $entityManager->getRepository('SmileEzTFABundle:TFA');

        $this->providersConfig = $providersConfig;
    }

    /**
     * Register new TFA Provider
     *
     * @param ProviderInterface $provider
     * @param string $alias TFA Provider identifier
     */
    public function addProvider(ProviderInterface $provider, $alias)
    {
        if ((isset($this->providersConfig[$alias])
                && (!isset($this->providersConfig[$alias]['disabled'])
                    || (isset($this->providersConfig[$alias]['disabled'])
                        && $this->providersConfig[$alias]['disabled'] !== true)))
            || !isset($this->providersConfig[$alias])
        ) {
            $this->providers[$alias] = $provider;
        }
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
}
