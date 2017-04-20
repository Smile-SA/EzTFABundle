<?php

namespace Smile\EzTFABundle\Provider\U2F\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AuthType
 * @package Smile\EzTFABundle\Provider\U2F\Form\Type
 */
class AuthType extends AbstractType
{
    /**
     * Construct U2F Auth form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_auth_code', HiddenType::class);
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * Get form block prefix
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'smileeztfa_provider_u2f_auth';
    }

    /**
     * Configure form
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'smileeztfa',
        ]);
    }
}
