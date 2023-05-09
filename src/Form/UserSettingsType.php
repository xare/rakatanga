<?php
namespace App\Form;

use Adamski\Symfony\PhoneNumberBundle\Form\PhoneNumberType;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsType extends AbstractType
{

  public function __construct(private TranslatorInterface $translator)
  {

  }
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
        ->add('email', EmailType::class)
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add(
              'telephone',
              PhoneNumberType::class,
              [
                  'label' => 'Phone number',
                  'preferred' => 'ES',
                  'required' => false,
              ]
            )
        ->add(
          'langue',
          ChoiceType::class,
          [
            'choices' => [
              'español' => 'es',
              'francés' => 'fr',
              'inglés' => 'en',
            ],
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
            'Portugal' => 'PT',
          ],
        ])
        ->add('sizes', ChoiceType::class, [
          'choices' => [
            'XS' => 'xs',
            'S' => 's',
            'M' => 'm',
            'L' => 'l',
            'XL' => 'xl',
            'XXL' => 'xxl',
          ],
        ])
        ->add('Register', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-success',
            ],
        ]);
        // add any other fields that the user can edit here
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
