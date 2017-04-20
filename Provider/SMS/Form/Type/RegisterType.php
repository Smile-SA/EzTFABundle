<?php

namespace Smile\EzTFABundle\Provider\SMS\Form\Type;

use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegisterType
 * @package Smile\EzTFABundle\Provider\SMS\Form\Type
 */
class RegisterType extends AbstractType
{
    /**
     * Construct sms register form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', PhoneNumberType::class, [
                'required' => true,
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'constraints' => array(new PhoneNumber())
            ])
            ->add('reegister', SubmitType::class, ['label' => 'sms.register']);
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
        return 'smileeztfa_provider_sms_register';
    }

    /**
     * Configure form
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'data_class' => 'Smile\EzTFABundle\Entity\TFASMS',
            'translation_domain' => 'smileeztfa',
        ]);
    }
}
