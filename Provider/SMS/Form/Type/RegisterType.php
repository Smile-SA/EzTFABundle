<?php

namespace Smile\EzTFABundle\Provider\SMS\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TextType::class, ['required' => true, 'label' => 'sms.phone'])
            ->add('reegister', SubmitType::class, ['label' => 'sms.register']);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'smileeztfa_provider_sms_register';
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'data_class' => 'Smile\EzTFABundle\Entity\TFASMS',
            'translation_domain' => 'smileeztfa',
        ]);
    }
}
