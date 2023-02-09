<?php

namespace App\Form;

use App\Entity\Travellers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Travellers1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('langue')
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('position')
            ->add('assurance')
            ->add('vols')
            ->add('date_ajout')
            ->add('email')
            ->add('user')
            ->add('date')
            ->add('reservation')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travellers::class,
        ]);
    }
}
