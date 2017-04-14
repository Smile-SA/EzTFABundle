<?php

namespace Smile\EzTFABundle\Provider\U2F\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyName', TextType::class, ['required' => true, 'label' => 'u2f.keyname'])
            ->add('_auth_code', HiddenType::class)
            ->add('register', ButtonType::class, ['attr' => ['onclick' => 'u2fauth.register();'], 'label' => 'u2f.register']);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'smileeztfa_provider_u2f_register';
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'translation_domain' => 'smileeztfa',
        ]);
    }
}
