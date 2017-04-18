<?php

namespace Smile\EzTFABundle\Provider\U2F;

use Doctrine\Bundle\DoctrineBundle\Registry;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Smile\EzTFABundle\Provider\ProviderAbstract;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Smile\EzTFABundle\Repository\TFAU2FRepository;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\Translator;

/**
 * Class U2FProvider
 * @package Smile\EzTFABundle\Provider\U2F
 */
class U2FProvider extends ProviderAbstract implements ProviderInterface
{
    /** @var Router $router */
    protected $router;

    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var \Doctrine\Common\Persistence\ObjectManager|object $entityManager */
    protected $entityManager;

    /** @var TFAU2FRepository $tfaU2FRepository */
    protected $tfaU2FRepository;

    /**
     * U2FProvider constructor.
     *
     * @param Router $router
     * @param Session $session
     * @param Translator $translator
     */
    public function __construct(
        Router $router,
        Session $session,
        Translator $translator,
        TokenStorage $tokenStorage,
        Registry $doctrineRegistry
    ) {
        parent::__construct($session, $translator);
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;

        $this->entityManager = $doctrineRegistry->getManager();
        /** @var TFAU2FRepository tfaU2FRepository */
        $this->tfaU2FRepository = $this->entityManager->getRepository('SmileEzTFABundle:TFAU2F');
    }

    /**
     * Return url to request auth code
     *
     * @param Request $request
     * @return string
     */
    public function requestAuthCode(Request $request)
    {
        $this->session->set('tfa_redirecturi', $request->getUri());

        return $this->router->generate('tfa_u2f_auth_form');
    }

    /**
     * Redirect user to register view
     *
     * @param TFARepository $tfaRepository
     * @param $userId
     * @param $provider
     * @return string
     */
    public function register(
        TFARepository $tfaRepository,
        $userId, $provider
    ) {
        return $this->router->generate('tfa_u2f_register_form');
    }

    public function getIdentifier()
    {
        return 'u2f';
    }

    public function getName()
    {
        return $this->translator->trans('u2f.provider.name', array(), 'smileeztfa');
    }

    public function getDescription()
    {
        return $this->translator->trans('u2f.provider.description', array(), 'smileeztfa');
    }

    public function canBeMultiple()
    {
        return true;
    }

    public function cancel()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        $u2fKeys = $this->tfaU2FRepository->findByUserId($apiUser->id);
        if ($u2fKeys) {
            foreach ($u2fKeys as $u2fKey) {
                $this->entityManager->remove($u2fKey);
                $this->entityManager->flush();
            }
        }
    }
}
