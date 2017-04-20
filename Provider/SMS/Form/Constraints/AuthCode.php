<?php

namespace Smile\EzTFABundle\Provider\SMS\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class AuthCode extends Constraint
{
    public function validatedBy()
    {
        return 'smileeztfa.sms.auth.contraint';
    }
}
