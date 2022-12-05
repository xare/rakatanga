<?php

namespace App\Form;

use App\Entity\Travellers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravellersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('langue', ChoiceType::class, [
                'choices' => [
                    'English' => 'en',
                    'Español' => 'es',
                    'Français' => 'fr',
                ],
            ])
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('position', ChoiceType::class, [
                'choices' => [
                    'Driver' => 'driver',
                    'Co-Driver' => 'codriver',
                ],
            ])
            ->add('assurance', ChoiceType::class, [
                'choices' => [
                    'Yes' => 'yes',
                    'No' => 'no',
                ],
            ])
            ->add('vols')
            ->add('date_ajout', DateType::class, [
                'empty_data' => '',
            ])
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Travellers::class,
        ]);
    }
}
