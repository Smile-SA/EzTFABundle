<?php

namespace Smile\EzTFABundle\Provider\SMS\Form\Type;

use Smile\EzTFABundle\Provider\SMS\Form\Constraints\AuthCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AuthType
 * @package Smile\EzTFABundle\Provider\SMS\Form\Type
 */
class AuthType extends AbstractType
{
    /**
     * Construct SMS Auth form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'required' => true,
                'label' => 'sms.code',
                'constraints' => array(new AuthCode())
            ])
            ->add('send', SubmitType::class, ['label' => 'sms.send']);
    }

    /**
     * Return form name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * Return form block prefix
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'smileeztfa_provider_sms_auth';
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
