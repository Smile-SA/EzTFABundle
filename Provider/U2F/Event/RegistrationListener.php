<?php

namespace Smile\EzTFABundle\Provider\U2F\Event;

use Smile\EzTFABundle\Entity\TFA;
use Smile\EzTFABundle\Repository\TFARepository;
use Smile\EzTFABundle\Repository\TFAU2FRepository;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class RegistrationListener
 * @package Smile\EzTFABundle\Provider\U2F\Event
 */
class RegistrationListener implements EventSubscriberInterface
{
    /** @var \Doctrine\Common\Persistence\ObjectManager|object $entityManager */
    protected $entityManager;

    /** @var TFAU2FRepository $tfaU2FRepository */
    protected $tfaU2FRepository;

    /** @var TFARepository $tfaRepository */
    protected $tfaRepository;

    /** @var Router $router */
    protected $router;

    /**
     * RegistrationListener constructor.
     *
     * @param Registry $doctrineRegistry
     * @param Router $router
     */
    public function __construct(
        Registry $doctrineRegistry,
        Router $router
    ) {
        $this->entityManager = $doctrineRegistry->getManager();
        $this->tfaU2FRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFAU2F');
        $this->tfaRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFA');

        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'smileez_tfa_u2f.register' => 'onRegister',
        );
    }

    /**
     * onRegister
     *
     * @param RegisterEvent $event
     * @return void
     **/
    public function onRegister(RegisterEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser($event);
        $apiUser = $user->getAPIUser();

        $registrationData = $event->getRegistration();

        $this->tfaU2FRepository->saveKey($registrationData, $apiUser->id, $event->getKeyName());
        /** @var TFA $userProvider */
        $userProvider = $this->tfaRepository->findOneByUserId($apiUser->id);
        if ($userProvider && $userProvider->getProvider() !== 'u2f') {
            $this->entityManager->remove($userProvider);
            $this->entityManager->flush();
        } else if (!$userProvider) {
            $this->tfaRepository->setProvider($apiUser->id, 'u2f');
        }

        $response = new RedirectResponse($this->router->generate('tfa_registered', ['provider' => 'u2f']));
        $event->setResponse($response);
    }
}
