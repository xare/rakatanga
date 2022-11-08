<?php

namespace App\Form;

use App\Entity\Dates;
use App\Entity\Options;
use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nbpilotes')
            ->add('nbAccomp')
            ->add('comment')
            ->add('log')
            ->add('status')
            
            /* ->add('date_ajout', DateTimeType::class)
            ->add('date_paiement', DateTimeType::class) */
            //->add('options', TextType::class)
            ->add('date', TextType::class)
            ->add('user', TextType::class)
            /* ->add('travellers', TextType::class) */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
