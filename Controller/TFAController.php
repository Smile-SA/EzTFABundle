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

    /** @var \Doctrine\Common\Persistence\ObjectManager|object $entityManager */
    protected $entityManager;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

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
        Registry $doctrineRegistry
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configResolver = $configResolver;

        $this->authHandler = $authHandler;
        $this->providers = $this->authHandler->getProviders();

        $this->entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFA');
    }

    /**
     * List all TFA Providers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user && $user instanceof User) {
            $apiUser = $user->getAPIUser();

            /** @var TFA $userProvider */
            $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);
            $providersList = array();

            foreach ($this->providers as $provider) {
                $providersList[$provider->getIdentifier()] = array(
                    'selected'    => ($userProvider && $userProvider->getProvider() == $provider->getIdentifier()) ? true : false,
                    'title'       => $provider->getName(),
                    'description' => $provider->getDescription()
                );
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
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user && $user instanceof User) {
            $apiUser = $user->getAPIUser();

            /** @var TFA $userProvider */
            $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);

            if ($userProvider) {
                $this->entityManager->remove($userProvider);
                $this->entityManager->flush();
            }

            $tfaProviders = $this->authHandler->getProviders();

            $tfaProvider = $tfaProviders[$provider];
            if ($redirect = $tfaProvider->register(
                $this->tfaRepository,
                $apiUser->id,
                $provider
            )
            ) {
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
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        /** @var TFA $userProvider */
        $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);

        if ($userProvider && $userProvider->getProvider() == $provider) {
            $this->entityManager->remove($userProvider);
            $this->entityManager->flush();
        }

        $redirectUrl = $this->generateUrl('tfa_list');
        return new RedirectResponse($redirectUrl);
    }
}
