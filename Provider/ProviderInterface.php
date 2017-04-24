<?php

namespace Smile\EzTFABundle\Provider;

use Smile\EzTFABundle\Repository\TFARepository;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\API\Repository\Values\User\User as APIUser;

/**
 * Interface ProviderInterface
 *
 * @package Smile\EzTFABundle\Provider
 */
interface ProviderInterface
{
    public function getIdentifier();

    public function getName();

    public function getDescription();

    public function isAuthenticated();

    public function requestAuthCode(Request $request);

    public function register(TFARepository $tfaRepository, $userId, $provider);

    public function canBeMultiple();

    public function cancel();

    public function purge(APIUser $user);
}
