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
use Symfony\Contracts\Translation\TranslatorInterface;

class DatesType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('travel', EntityType::class, [
                'class' => Travel::class,
                'placeholder' => 'Elige Viaje: '
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dates::class,
        ]);
    }
}
