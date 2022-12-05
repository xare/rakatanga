<?php

namespace App\Form;

use App\Entity\Dates;
use App\Entity\Travel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('travel', EntityType::class, [
                'class' => Travel::class,
                'placeholder' => 'Elige Viaje: ',
                'choice_label' => 'main_title',
            ])
            ->add('debut', null, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('fin', null, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('prixPilote', MoneyType::class, [
                'currency' => 'EUR',
                'html5' => true,
            ])
            ->add('prixAccomp', MoneyType::class, [
                'currency' => 'EUR',
                'html5' => true,
            ])
            ->add('statut', ChoiceType::class, [
                'placeholder' => 'Selecciona el estatus: ',
                'choices' => [
                    'open' => 'abierto',
                    'full' => 'completo',
                    'private' => 'privado',
                    'closed' => 'closed',
                ],
            ])
            ->add('requestedDocs', ChoiceType::class, [
                'placeholder' => 'Selecciona los documentos requeridos: ',
                'choices' => [
                    'Passport' => 'passport',
                    'Drivers Licence' => 'drivers_license',
                    'Insurance' => 'insurance',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dates::class,
        ]);
    }
}
