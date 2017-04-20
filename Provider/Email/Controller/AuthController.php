<?php

namespace Smile\EzTFABundle\Provider\Email\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AuthController
 * @package Smile\EzTFABundle\Provider\Email\Controller
 */
class AuthController extends Controller
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var ConfigResolverInterface $configResolver */
    protected $configResolver;

    /** @var \Swift_Mailer $mailer */
    protected $mailer;

    /** @var TranslatorInterface $translator */
    protected $translator;

    /** @var array $providers */
    protected $providers;

    /** @var Session $session */
    protected $session;

    /**
     * AuthController constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param ConfigResolverInterface $configResolver
     * @param \Swift_Mailer $mailer
     * @param TranslatorInterface $translator
     * @param array $providers
     * @param Session $session
     */
    public function __construct(
        TokenStorage $tokenStorage,
        ConfigResolverInterface $configResolver,
        \Swift_Mailer $mailer,
        TranslatorInterface $translator,
        array $providers,
        Session $session
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configResolver = $configResolver;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->providers = $providers;

        $this->session = $session;
    }

    /**
     * Send TFA code by email
     *
     * @param string $code
     * @param string $emailFrom
     * @param string $emailTo
     */
    protected function sendCode($code, $emailFrom, $emailTo)
    {
        $message = \Swift_Message::newInstance();

        $message->setSubject($this->translator->trans('Two Factor Authentication code', array(), 'smileeztfa'))
            ->setFrom($emailFrom)
            ->setTo($emailTo)
            ->setBody(
                $this->renderView(
                    'SmileEzTFABundle:tfa:email/mail.txt.twig',
                    array(
                        'code' => $code
                    )
                ), 'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * Ask for TFA code authentication
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authAction(Request $request)
    {
        $actionUrl = $this->generateUrl('tfa_email_auth_form');

        $form = $this->createForm('Smile\EzTFABundle\Provider\Email\Form\Type\AuthType');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->session->set('tfa_authenticated', true);
            return new RedirectResponse($this->session->get('tfa_redirecturi'));
        }

        $code = $this->session->get('tfa_authcode', false);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $apiUser = $user->getAPIUser();
        $mailTo = $apiUser->email;
        $mailFrom = $this->providers['email']['from'];

        $codeSended = $this->session->get('tfa_codesended', false);
        if (!$codeSended) {
            $this->sendCode($code, $mailFrom, $mailTo);
            $this->session->set('tfa_codesended', true);
        }

        return $this->render('SmileEzTFABundle:tfa:email/auth.html.twig', [
            'layout' => $this->configResolver->getParameter('pagelayout'),
            'form' => $form->createView(),
            'actionUrl' => $actionUrl
        ]);
    }
}
