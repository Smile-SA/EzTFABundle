<?php

namespace Smile\EzTFABundle\Provider\SMS\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Smile\EzTFABundle\Entity\TFASMS;
use Smile\EzTFABundle\Repository\TFASMSRepository;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Ovh\Api;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

class AuthController extends Controller
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var ConfigResolverInterface $configResolver */
    protected $configResolver;

    /** @var TranslatorInterface $translator */
    protected $translator;

    /** @var array $providers */
    public $providers;

    /** @var TFASMSRepository $tfaSMSRepository */
    protected $tfaSMSRepository;

    /** @var Session $session */
    protected $session;

    /**
     * AuthController constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(
        TokenStorage $tokenStorage,
        ConfigResolverInterface $configResolver,
        TranslatorInterface $translator,
        array $providers,
        Registry $doctrineRegistry,
        Session $session
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configResolver = $configResolver;
        $this->translator = $translator;
        $this->providers = $providers;

        $entityManager = $doctrineRegistry->getManager();
        $this->tfaSMSRepository = $entityManager->getRepository('SmileEzTFABundle:TFASMS');

        $this->session = $session;
    }

    /**
     * Send SMS Code
     *
     * @param string $code SMS code
     */
    protected function sendCode($code, $phoneNumber)
    {
        $endpoint = 'ovh-eu';

        $smsConn = new Api(
            $this->providers['sms']['application_key'],
            $this->providers['sms']['application_secret'],
            $endpoint,
            $this->providers['sms']['consumer_key']
        );

        $message = $this->renderView(
            'SmileEzTFABundle:tfa:sms/sms.txt.twig',
            array(
                'code' => $code
            )
        );

        $smsServices = $smsConn->get('/sms/');

        $content = (object) array(
            "charset" => "UTF-8",
            "class" => "phoneDisplay",
            "coding" => "7bit",
            "message" => $message,
            "noStopClause" => false,
            "priority" => "high",
            "receivers" => [ $phoneNumber ],
            "senderForResponse" => true,
            "validityPeriod" => 2880
        );
        $smsConn->post('/sms/'. $smsServices[0] . '/jobs/', $content);
    }

    public function authAction(Request $request)
    {
        $actionUrl = $this->generateUrl('tfa_sms_auth_form');

        $form = $this->createForm('Smile\EzTFABundle\Provider\SMS\Form\Type\AuthType');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $TFACode = $this->session->get('tfa_authcode', false);
            $data = $form->getData();
            $code = (int)$data['code'];

            if ($code !== $TFACode)
                return $this->redirectToRoute('tfa_sms_auth_form');

            $this->session->set('tfa_authenticated', true);
            return new RedirectResponse($this->session->get('tfa_redirecturi'));
        }

        $code = $this->session->get('tfa_authcode', false);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();

        /** @var TFASMS $userPhone */
        $userPhone = $this->tfaSMSRepository->findOneByUserId($apiUser->id);

        $codeSended = $this->session->get('tfa_codesended', false);
        if (!$codeSended) {
            $this->sendCode($code, $userPhone->getPhone());
            $this->session->set('tfa_codesended', true);
        }

        return $this->render('SmileEzTFABundle:tfa:sms/auth.html.twig', [
            'layout' => $this->configResolver->getParameter('pagelayout'),
            'form' => $form->createView(),
            'actionUrl' => $actionUrl
        ]);
    }
}
