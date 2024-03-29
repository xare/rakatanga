<?php

namespace App\Form;

use App\Entity\Options;
use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('nbpilotes', HiddenType::class)
            ->add('nbAccomp', HiddenType::class)
            ->add('comment', HiddenType::class)
            ->add('note')
            ->add('status', ChoiceType::class,[
                                'choices' => [
                                    'Initialized' => 'yes',
                                    'paid' => 'no',
                                ],])

            /* ->add('date_ajout', DateTimeType::class)
            ->add('date_paiement', DateTimeType::class) */
            // ->add('options', TextType::class)
            ->add('date')
            /* ->add('user', EntityType::class, [
                'class' => User::class,
                'placeholder' => 'Elige Usuario: ',
                'choice_label' => 'email',
            ]) */
            //->add('user', TextType::class)
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
