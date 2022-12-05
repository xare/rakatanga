<?php

namespace App\Form;

use App\Entity\Continents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContinentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('continentTranslation', CollectionType::class, [
                'entry_type' => ContinentTranslationType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true,
            ])
           /*  ->addEventListener(
                FormEvents::POST_SET_DATA,
                array($this, 'onPostSetData')
            ) */
        ;
    }

    /* public function onPostSetData(FormEvent $event){
        if($event->getData() && $event->getData()->getId()){
            $form = $event->getForm();

            $form->add('code')
            ->add('continentTranslation', CollectionType::class, [
                'entry_type' => ContinentTranslationType::class,
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
            'data_class' => Continents::class,
            'allow_extra_fields' => true,
            'csrf_field_name' => '_token',
        ]);
    }
}
