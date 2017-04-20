<?php

namespace Smile\EzTFABundle\Provider\Email\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class AuthCode extends Constraint
{
    public function validatedBy()
    {
        return 'smileeztfa.email.auth.contraint';
    }
}
