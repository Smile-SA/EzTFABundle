<?php

namespace Smile\EzTFABundle\Provider\U2F\Form\Constraints;

use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Smile\EzTFABundle\Provider\U2F\Security\Authenticator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AuthCodeValidator extends ConstraintValidator
{
    /** @var Authenticator $authenticator */
    protected $authenticator;

    /** @var User $user */
    protected $user;

    /** @var Session $session */
    protected $session;

    public function __construct(
        Authenticator $authenticator,
        TokenStorage $tokenStorage,
        Session $session
    ) {
        $this->authenticator = $authenticator;
        $this->session = $session;

        /** @var User $user */
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->authenticator->checkRequest(
            $this->user,
            json_decode($this->session->get('u2f_authentication')),
            json_decode($value)
        )) {
            $this->context->addViolation('Authentication code do not match');

            return;
        }
    }
}
