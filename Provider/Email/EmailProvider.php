<?php

namespace Smile\EzTFABundle\Provider\Email;

use Smile\EzTFABundle\Provider\ProviderAbstract;
use Smile\EzTFABundle\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EmailProvider
 * @package Smile\EzTFABundle\Provider\Email
 */
class EmailProvider extends ProviderAbstract implements ProviderInterface
{
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

        $siteaccessUrl = $this->getSiteaccessUrl($request);
        $redirectUrl = $siteaccessUrl . '/_tfa/email/auth';

        return $redirectUrl;
    }

    public function getIdentifier()
    {
        return 'email';
    }

    public function getName()
    {
        return $this->translator->trans('email.provider.name', array(), 'smileeztfa');
    }

    public function getDescription()
    {
        return $this->translator->trans('email.provider.description', array(), 'smileeztfa');
    }
}
