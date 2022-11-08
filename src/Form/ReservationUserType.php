<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Model\DateReservationFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            /* ->add('nbpilotes', ChoiceType::class,[
                'choices' =>$numbers,
                'label' => 'Número de pilotos'
            ])
            ->add('nbaccomp', ChoiceType::class,[
                'choices' =>$numbers,
                'label' => 'Número de pasajeros'
            ]) */
            ->add('email')
            /* ->add('roles') */
            ->add('password', PasswordType::class,[
                'empty_data' => ''
            ])
            ->add('langue')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('position', ChoiceType::class,[
                'choices' => [
                    'Pilot' => 'pilot',
                    'Passenger' => 'passenger',
                ]
            ])
            ->add('travellers', CollectionType::class, [
                'entry_type' => TravellersType::class ,
                'entry_options' => [
                    'label' => false
                ],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true
            ])
            ->add('idcard', TextType::class)
            ->add('address', TextType::class)
            ->add('postcode', TextType::class)
            ->add('city', TextType::class)
            ->add('country', CountryType::class )
            ->add('nationality', TextType::class)
            ->add('Register', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-success',
                    'value' => "Haz tu reserva"
                ]
                ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => User::class,
            'data_class' => DateReservationFormModel::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
