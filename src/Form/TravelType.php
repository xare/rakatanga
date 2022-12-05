<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Media;
use App\Entity\Travel;
use App\Repository\MediaRepository;
use App\Repository\TravelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TravelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('main_title', TextType::class)
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Principiante' => 'deb',
                    'Intermediario' => 'int',
                    'Experto' => 'exp'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Se muestra en la web' => 'oui',
                    'Todavía en preparación' => 'non' 
                ],
            ])
            ->add('duration')
            ->add('km')
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'placeholder' => 'Elige categoría',
                'choice_label' => 'name',
            ])
            
            ->add('travelTranslation', CollectionType::class, [
                'entry_type' => TravelTranslationType::class,
                'entry_options' => [
                    'label' => false
                ],
                
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true
            ]) 
    
        ;
    }
    public function onPreSetData(FormEvent $event){
        $travel = $event->getData();
        
        $form = $event->getForm();
        $form
        ->add('media', EntityType::class, [
            'class' => Media::class,
            'choice_label' => 'name',
            'query_builder' => function(MediaRepository $mediaRepository) use ($travel) {
                //return $mediaRepository->findBy(['travel' => $travel->getId()]);
                return $travel->getMedia();
            },
            'multiple' => true
        ]);

    }
    /* public function onPostSetData(FormEvent $event){
        if($event->getData() && $event->getData()->getId()){
            $form = $event->getForm();
            
            $form->add('main_title', TextType::class)
            ->add('level', ChoiceType::class, [
                'choices' => [
                    'Principiante' => 'deb',
                    'Intermediario' => 'int',
                    'Experto' => 'exp'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Se muestra en la web' => 'oui',
                    'Todavía en preparación' => 'non' 
                ],
            ])
            ->add('duration')
            ->add('km')
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'placeholder' => 'Elige categoría',
                'choice_label' => 'name',
                'query_builder' => function(EntityRepository $er) {
                                        return $er->createQueryBuilder('c')
                                                ->andWhere('c.type = :type')
                                                ->setParameter('type', 'country')
                                                ->orderBy('c.name', 'ASC');
                },
            ])
             ->add('media', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('travelTranslation', CollectionType::class, [
                'entry_type' => TravelTranslationType::class,
                'entry_options' => [
                    'label' => false
                ],
                'prototype' => false,
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true
            ]); 
        }
    } */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
            'allow_extra_fields' => true
        ]);
    }
}
