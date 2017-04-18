<?php

namespace Smile\EzTFABundle\Provider;

use Smile\EzTFABundle\Repository\TFARepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;

/**
 * Class ProviderAbstract
 *
 * @package Smile\EzTFABundle\Provider
 */
class ProviderAbstract
{
    /** @var Session $session */
    protected $session;

    /** @var Translator $translator */
    protected $translator;

    /**
     * ProviderAbstract constructor.
     *
     * @param Session $session
     * @param Translator $translator
     */
    public function __construct(
        Session $session,
        Translator $translator
    ) {
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * Check if user is TFA authenticated
     *
     * @return mixed
     */
    public function isAuthenticated()
    {
        return $this->session->get('tfa_authenticated', false);
    }

    /**
     * Return siteaccess host
     *
     * @param Request $request
     * @return string
     */
    protected function getSiteaccessUrl(Request $request)
    {
        $semanticPathinfo = $request->attributes->get('semanticPathinfo') ?: '/';
        $semanticPathinfo = rtrim($semanticPathinfo, '/');
        $uri = $request->getUri();
        if (!$semanticPathinfo)
            return $uri;

        return substr($uri, 0, -strlen($semanticPathinfo));
    }

    /**
     * Register for current user TFA Provider activated
     * 
     * @param TFARepository $tfaRepository
     * @param $userId
     * @param $provider
     * @return null
     */
    public function register(
        TFARepository $tfaRepository,
        $userId, $provider
    ) {
        $tfaRepository->setProvider($userId, $provider);

        return null;
    }

    public function canBeMultiple()
    {
        return false;
    }
}
