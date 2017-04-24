<?php

namespace Smile\EzTFABundle\Provider\SMS;

use Smile\EzTFABundle\Provider\ProviderAbstract;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Smile\EzTFABundle\Repository\TFASMSRepository;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class SMSProvider
 * @package Smile\EzTFABundle\Provider\SMS
 */
class SMSProvider extends ProviderAbstract implements ProviderInterface
{
    /** @var Router $router */
    protected $router;

    /** @var TFASMSRepository $tfaSMSRepository */
    protected $tfaSMSRepository;

    /**
     * SMSProvider constructor.
     *
     * @param Router $router
     * @param Session $session
     * @param Translator $translator
     */
    public function __construct(
        Router $router,
        Session $session,
        Translator $translator,
        Registry $doctrineRegistry
    ) {
        parent::__construct($session, $translator);
        $this->router = $router;

        $entityManager = $doctrineRegistry->getManager();
        /** @var TFASMSRepository tfaSMSRepository */
        $this->tfaSMSRepository = $entityManager->getRepository('SmileEzTFABundle:TFASMS');
    }

    /**
     * Return url to request auth code
     *
     * @param Request $request
     * @return string
     */
    public function requestAuthCode(Request $request)
    {
        $authCode = random_int(100000, 999999);
        $this->session->set('tfa_authcode', $authCode);
        $this->session->set('tfa_redirecturi', $request->getUri());

        $redirectUrl =  $this->router->generate('tfa_sms_auth_form');

        return $redirectUrl;
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
        return $this->router->generate('tfa_sms_register_form');
    }

    public function getIdentifier()
    {
        return 'sms';
    }

    public function getName()
    {
        return $this->translator->trans('sms.provider.name', array(), 'smileeztfa');
    }

    public function getDescription()
    {
        return $this->translator->trans('sms.provider.description', array(), 'smileeztfa');
    }

    public function purge(APIUser $user)
    {
        $this->tfaSMSRepository->purge($user);
    }
}
