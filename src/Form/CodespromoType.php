<?php

namespace App\Form;

use App\Entity\Codespromo;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodespromoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code')
            ->add('libelle')
            ->add('commentaire')
            ->add('montant')
            ->add('pourcentage')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Número de utilizaciones' => 'uses',
                    'Periodo de utilizaciones' => 'period',
                ],
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('nombre')
            ->add('nombreTotal')
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
            ->add('email')
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'actif' => 'actif',
                    'inactif' => 'inactif',
                ],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return sprintf('%s [%s]', $user->getEmail(), $user->getNom().' '.$user->getPrenom());
                },
                'required' => false,
                'placeholder' => 'Elegir un valor',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Codespromo::class,
        ]);
    }
}
