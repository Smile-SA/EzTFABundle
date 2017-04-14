<?php

namespace Smile\EzTFABundle\Provider\U2F;

use Smile\EzTFABundle\Provider\ProviderAbstract;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Smile\EzTFABundle\Repository\TFARepository;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;

class U2FProvider extends ProviderAbstract implements ProviderInterface
{
    /** @var Router $router */
    protected $router;

    public function __construct(
        Router $router,
        Session $session,
        Translator $translator
    ) {
        parent::__construct($session, $translator);
        $this->router = $router;
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

    public function register(
        TFARepository $tfaRepository,
        $userId, $provider
    )
    {
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
}
