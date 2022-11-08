<?php

namespace App\Form;

use App\Entity\ContinentTranslation;
use App\Entity\Lang;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContinentTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name')
            ->add('continents')
            ->add('lang', EntityType::class,[
                'class' => Lang::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
            ])
             /* ->addEventListener(
                FormEvents::POST_SET_DATA,
                array($this, 'onPostSetData')
            ) */
        ;
    }
/* 
    public function onPostSetData(FormEvent $event){
        if($event->getData() && $event->getData()->getId()){
            $form = $event->getForm();
            
            return $form->getData();
        }

    } */

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContinentTranslation::class,
            'allow_extra_fields' => true
        ]);
    }
}
