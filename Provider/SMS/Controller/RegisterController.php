<?php

namespace Smile\EzTFABundle\Provider\SMS\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use libphonenumber\PhoneNumber;
use Smile\EzTFABundle\Entity\TFASMS;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Smile\EzTFABundle\Repository\TFASMSRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class RegisterController
 * @package Smile\EzTFABundle\Provider\SMS\Controller
 */
class RegisterController extends Controller
{
    /** @var ConfigResolverInterface $configResolver */
    protected $configResolver;

    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var \Doctrine\Common\Persistence\ObjectManager|object $entityManager */
    protected $entityManager;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    /** @var TFASMSRepository $tfaSMSRepository  */
    protected $tfaSMSRepository;

    /** @var ProviderInterface $provider */
    protected $provider;

    /**
     * RegisterController constructor.
     *
     * @param ConfigResolverInterface $configResolver
     * @param TokenStorage $tokenStorage
     * @param Registry $doctrineRegistry
     * @param ProviderInterface $provider
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        TokenStorage $tokenStorage,
        Registry $doctrineRegistry,
        ProviderInterface $provider
    ) {
        $this->configResolver = $configResolver;
        $this->tokenStorage = $tokenStorage;

        $this->entityManager = $doctrineRegistry->getManager();
        $this->tfaRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFA');
        $this->tfaSMSRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFASMS');

        $this->provider = $provider;
    }

    /**
     * Ask user form phone number and register TFA Provider configuration
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $actionUrl = $this->generateUrl('tfa_sms_register_form');
        $redirectUrl = $this->generateUrl('tfa_registered', ['provider' => $this->provider->getIdentifier()]);

        $TFASMS = new TFASMS();
        $form = $this->createForm('Smile\EzTFABundle\Provider\SMS\Form\Type\RegisterType', $TFASMS);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PhoneNumber $phoneObject */
            $phoneObject = $TFASMS->getPhone();
            $phoneNumber = '+' . $phoneObject->getCountryCode() . $phoneObject->getNationalNumber();

            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $apiUser = $user->getAPIUser();

            /** @var TFASMSRepository $userSMS */
            $userSMS = $this->tfaSMSRepository->findOneByUserId($apiUser->id);
            if ($userSMS) {
                $this->entityManager->remove($userSMS);
                $this->entityManager->flush();
            }
            $this->tfaSMSRepository->savePhone($apiUser->id, $phoneNumber);
            $this->tfaRepository->setProvider($apiUser->id, $this->provider->getIdentifier());

            return new RedirectResponse($redirectUrl);
        }

        return $this->render('SmileEzTFABundle:tfa:sms/register.html.twig', [
            'layout' => $this->configResolver->getParameter('pagelayout'),
            'form' => $form->createView(),
            'actionUrl' => $actionUrl
        ]);
    }
}
