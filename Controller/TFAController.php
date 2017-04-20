<?php

namespace Smile\EzTFABundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzTFABundle\Entity\TFA;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Smile\EzTFABundle\Security\AuthHandler;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class TFAController
 * @package Smile\EzTFABundle\Controller
 */
class TFAController extends Controller
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var ConfigResolverInterface $configResolver */
    protected $configResolver;

    /** @var ProviderInterface[] $providers */
    protected $providers;

    /** @var AuthHandler $authHandler */
    protected $authHandler;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    protected $providersConfig;

    /**
     * TFAController constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param ConfigResolverInterface $configResolver
     * @param AuthHandler $authHandler
     * @param Registry $doctrineRegistry
     */
    public function __construct(
        TokenStorage $tokenStorage,
        ConfigResolverInterface $configResolver,
        AuthHandler $authHandler,
        Registry $doctrineRegistry,
        $providersConfig
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configResolver = $configResolver;

        $this->authHandler = $authHandler;
        $this->providers = $this->authHandler->getProviders();

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $entityManager->getRepository('SmileEzTFABundle:TFA');

        $this->providersConfig = $providersConfig;
    }

    /**
     * List all TFA Providers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user && $user instanceof User) {
            $apiUser = $user->getAPIUser();

            /** @var TFA $userProvider */
            $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);
            $providersList = array();

            foreach ($this->providers as $provider) {
                $identifier = $provider->getIdentifier();

                if ((isset($this->providersConfig[$identifier])
                    && (!isset($this->providersConfig[$identifier]['disabled'])
                    || (isset($this->providersConfig[$identifier]['disabled'])
                    && $this->providersConfig[$identifier]['disabled'] !== true)))
                    || !isset($this->providersConfig[$identifier])
                ) {
                    $providersList[$provider->getIdentifier()] = array(
                        'selected'    => ($userProvider && $userProvider->getProvider() == $provider->getIdentifier()) ? true : false,
                        'title'       => $provider->getName(),
                        'description' => $provider->getDescription()
                    );
                }
            }

            return $this->render('SmileEzTFABundle:tfa:list.html.twig', [
                'layout'        => $this->configResolver->getParameter('pagelayout'),
                'providersList' => $providersList
            ]);
        }
    }

    /**
     * Activate specific TFA Provider
     *
     * @param string $provider TFA Provider identifier
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function clickAction($provider)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user && $user instanceof User) {
            $apiUser = $user->getAPIUser();

            /** @var TFA $userProvider */
            $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);

            if ($userProvider
                && !$this->providers[$userProvider->getProvider()]->canBeMultiple()
            ) {
                $this->tfaRepository->remove($userProvider);
            }

            $tfaProviders = $this->authHandler->getProviders();

            $tfaProvider = $tfaProviders[$provider];
            if ($redirect = $tfaProvider->register(
                $this->tfaRepository,
                $apiUser->id,
                $provider
            )) {
                return $this->redirect($redirect);
            }

            return $this->render('SmileEzTFABundle:tfa:click.html.twig', [
                'layout'   => $this->configResolver->getParameter('pagelayout'),
                'provider' => $provider
            ]);
        }
    }

    /**
     * Return message when TFA Provider activated
     *
     * @param string $provider TFA Provider identifier
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registeredAction($provider)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('SmileEzTFABundle:tfa:click.html.twig', [
            'layout' => $this->configResolver->getParameter('pagelayout'),
            'provider' => $provider
        ]);
    }

    /**
     * Reinitialize TFA Provider configuration
     *
     * @param string $provider TFA Provider identifier
     * @return RedirectResponse
     */
    public function reinitializeAction($provider)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $redirectUrl = $this->generateUrl('tfa_click', ['provider' => $provider]);
        return new RedirectResponse($redirectUrl);
    }

    /**
     * Cancel TFA Provider previously activated
     *
     * @param string $provider TFA Provider identifier
     * @return RedirectResponse
     */
    public function cancelAction($provider)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        /** @var TFA $userProvider */
        $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);

        if ($userProvider && $userProvider->getProvider() == $provider) {
            $this->tfaRepository->remove($userProvider);
            if (isset($this->providers[$provider])) {
                $this->providers[$provider]->cancel();
            }
        }

        $redirectUrl = $this->generateUrl('tfa_list');
        return new RedirectResponse($redirectUrl);
    }
}
