<?php

namespace App\Form;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Adamski\Symfony\PhoneNumberBundle\Form\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            //->add('roles', ChoiceType::class, [
            //  'choices' => [
            //    'Admin' => null,
            //    'User' => true
            //  ]
            //])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add(
                'telephone',
                PhoneNumberType::class,
                [
                    "label"     => "Phone number",
                    "preferred" => "ES",
                    "required"  => false
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'empty_data' => ''
                ]
            )
            ->add(
                'langue',
                LanguageType::class,
                [
                    'preferred_choices' => [
                        'español' => 'es',
                        'francés' => 'fr',
                        'inglés' => 'en',
                        'alemán' => 'de'
                    ]
                ]
            )
            ->add('address', TextType::class)
            ->add('postcode', TextType::class)
            ->add('city', TextType::class)
            ->add('idcard', TextType::class)
            ->add('country', CountryType::class)
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [
                    'España' => 'ES',
                    'Francia' => 'FR',
                    'Reino Unido' => 'GB',
                    'Portugal' => 'PT'
                ]
            ])
            ->add('position', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Piloto') => 'pilot',
                    $this->translator->trans('Acompañante') => 'passenger',
                ]
            ])
            ->add('sizes', ChoiceType::class, [
                'choices' => [
                    'SL' => "xl",
                    'S' => "sl",
                    'M' => "m",
                    'L' => "l",
                    'XL' => "xl",
                    'XXL' => "xxl"
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'I know, it\'s silly, but you must agree to our terms.'
                    ])
                ]
            ])
            ->add('Register', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
