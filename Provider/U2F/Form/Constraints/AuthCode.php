<?php

namespace Smile\EzTFABundle\Provider\U2F\Form\Constraints;

use Symfony\Component\Validator\Constraint;

class AuthCode extends Constraint
{
    public function validatedBy()
    {
        return 'smileeztfa.u2f.auth.contraint';
    }
}
