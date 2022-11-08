<?php

namespace App\Form;

use App\Entity\Dates;
use App\Entity\Inscriptions;
use App\Entity\Oldreservations;
use App\Entity\Travel;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OldreservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OldReservations|null $oldReservation */
        $oldReservation = $options['data'] ?? null;
        /* $travel = $oldReservation ? $oldReservation->getTravel() : null; */

        $builder
            ->add('langue')
            ->add('nbpilotes')
            ->add('nbAccomp')
            ->add('commentaire')
            ->add('log')
            ->add('codepromo')
            ->add('montant')
            ->add('reduction')
            ->add('totalttc')
            ->add('notes')
            ->add('statut')
            ->add('origine_ajout')
            ->add('date_ajout', null, [
                'widget' => 'single_text' ,
                'html5' => false,
                'attr' =>
                    [ 'class' => 'datepicker' ]
                ])
            ->add('date_paiement_1', null, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => 
                    [ 'class' => 'datepicker' ]
            ])
            ->add('date_paiement_2', null, [
                'widget' => 'single_text',
                'html5'=> false,
                'attr' =>
                    [ 'class' => 'datepicker' ]
            ])
            ->add('Inscriptions', EntityType::class,[
                'class' => Inscriptions::class,
                'placeholder' => 'Elige Inscrito',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('Travel', EntityType::class,[
                'class' => Travel::class,
                'placeholder' => 'Elige Viaje',
                'multiple' => false,
                'expanded' => false,
            ])
            /* ->add('Dates', EntityType::class,[
                'class' => Dates::class,
                'placeholder' => 'Elige Fecha',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.debut', 'ASC');
                },
                'multiple' => false,
                'expanded' => false,
            ]) */
        ;
        
        /* if ($travel) {
            $builder->add('dates', EntityType::class, [
                'class' => Dates::class,
                'placeholder' => 'Elige Fecha',
                'choices' => $travel->getDates(),
                /* 'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.debut', 'ASC');
                }, */
                /* 'multiple' => false,
                'expanded' => false, */
           /* ]);
        } */
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var OldReservation|null $data */
                $data = $event->getData();
                if(!$data) {
                    return;
                }
                $this->setupDateNameField(
                    $event->getForm(),
                    $data->getTravel()
                );
            }
        );
        $builder->get('Travel')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event){
                $form = $event->getForm();
                $this->setupDateNameField(
                    $form->getParent(),
                    $form->getData()
                );
            }
        );
    }

    private function setupDateNameField(FormInterface $form, ?Travel $Travel)
    {
        if(null === $Travel){
            $form->remove('dates');
            return;
        }
        $dates = $Travel->getDates();
        if(null === $dates) {
            $form->remove('dates');
            return;
        }
        $form->add('dates', EntityType::class, [
            'class' => Dates::class,
            'placeholder' => 'Elige Fecha',
            'choices' => $dates,
            /* 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->orderBy('d.debut', 'ASC');
            }, */
            'multiple' => false,
            'expanded' => false,
        ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oldreservations::class,
        ]);
    }
}
