<?php

namespace Smile\EzTFABundle\Provider\U2F\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzTFABundle\Entity\TFA;
use Smile\EzTFABundle\Entity\TFAU2F;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Provider\U2F\Event\RegisterEvent;
use Smile\EzTFABundle\Provider\U2F\Security\Authenticator;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Smile\EzTFABundle\Repository\TFARepository;
use Smile\EzTFABundle\Repository\TFAU2FRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class RegisterController
 * @package Smile\EzTFABundle\Provider\U2F\Controller
 */
class RegisterController extends Controller
{
    /** @var ConfigResolverInterface $configResolver */
    protected $configResolver;

    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var Authenticator $authenticator */
    protected $authenticator;

    /** @var ProviderInterface $provider */
    protected $provider;

    /** @var  Session $session */
    protected $session;

    /** @var TFAU2FRepository $tfaU2FRepository */
    protected $tfaU2FRepository;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    /**
     * RegisterController constructor.
     *
     * @param ConfigResolverInterface $configResolver
     * @param TokenStorage $tokenStorage
     * @param Authenticator $authenticator
     * @param ProviderInterface $provider
     * @param Session $session
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        TokenStorage $tokenStorage,
        Authenticator $authenticator,
        ProviderInterface $provider,
        Registry $doctrineRegistry,
        Session $session
    ) {
        $this->configResolver = $configResolver;
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->provider = $provider;
        $this->session = $session;

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaU2FRepository = $entityManager->getRepository('SmileEzTFABundle:TFAU2F');
        $this->tfaRepository = $entityManager->getRepository('SmileEzTFABundle:TFA');
    }

    /**
     * Ask user for key and key name to register new TFA U2F provider configuration
     *
     * @param Request $request
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $actionUrl = $this->generateUrl('tfa_u2f_register_form');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $form = $this->createForm('Smile\EzTFABundle\Provider\U2F\Form\Type\RegisterType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $registerData = json_decode($data['_auth_code']);
            $registrationRequest = json_decode($this->session->get('u2f_registrationRequest'));
            $registration = $this->authenticator->doRegistration($registrationRequest[0], $registerData);

            $dispatcher = $this->get('event_dispatcher');
            $event = new RegisterEvent($registration, $user, $data['keyName']);
            $dispatcher->dispatch('smileez_tfa_u2f.register', $event);

            return $event->getResponse();
        }

        $registrationRequest = $this->authenticator->generateRegistrationRequest($user);
        $this->session->set('u2f_registrationRequest', json_encode($registrationRequest));

        return $this->render('SmileEzTFABundle:tfa:u2f/register.html.twig', [
            'layout' => $this->configResolver->getParameter('pagelayout'),
            'form' => $form->createView(),
            'registered' => $this->getRegistered(),
            'actionUrl' => $actionUrl,
            'registrationRequest' => json_encode($registrationRequest, JSON_UNESCAPED_SLASHES),
        ]);
    }

    /**
     * Remove registered U2F Key and redirect to register form
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($id)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        /** @var TFAU2F $u2fKey */
        $u2fKey = $this->tfaU2FRepository->findOneById($id);
        if ($u2fKey && $u2fKey->getUserId() == $apiUser->id) {
            $this->tfaU2FRepository->remove($u2fKey);

            $u2fKeys = $this->getRegistered();
            if (!$u2fKeys) {
                /** @var TFA $userTFA */
                $userTFA = $this->tfaRepository->findOneByUserId($apiUser->id);
                if ($userTFA) {
                    $this->tfaRepository->remove($userTFA);
                }
            }
        }

        return $this->redirectToRoute('tfa_u2f_register_form');
    }

    /**
     * List U2F keys registered
     *
     * @return mixed
     */
    protected function getRegistered()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        return $this->tfaU2FRepository->findByUserId($apiUser->id);
    }
}
