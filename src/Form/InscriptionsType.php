<?php

namespace App\Form;

use App\Entity\Inscriptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('email')
            ->add('position')
            ->add('arrhes')
            ->add('solde')
            ->add('assurance')
            ->add('vols')
            ->add('statut')
            ->add('remarque')
            ->add('date_ajout', null, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('langue')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscriptions::class,
        ]);
    }
}
