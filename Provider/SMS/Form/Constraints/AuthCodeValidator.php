<?php

namespace Smile\EzTFABundle\Provider\SMS\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthCodeValidator extends ConstraintValidator
{
    /** @var Session $session */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $TFACode = $this->session->get('tfa_authcode', false);
        $code = (int)$value;

        if ($code !== $TFACode) {
            $this->context->addViolation('Authentication code do not match');

            return;
        }
    }
}
