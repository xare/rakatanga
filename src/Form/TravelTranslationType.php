<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\TravelTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravelTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextType::class)
            ->add('lang', EntityType::class, [
                'class' => Lang::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
            ])
            // ->add('url')
            ->add('summary', TextType::class)
            ->add('intro', TextType::class)
            ->add('description', TextType::class)
            ->add('itinerary', TextType::class)
            ->add('practical_info', TextType::class)
            /* ->addEventListener(
                FormEvents::POST_SET_DATA,
                array($this, 'onPostSetData')
            ) */
        ;
    }

    /* public function onPostSetData(FormEvent $event){
        if($event->getData() && $event->getData()->getId()){
            $form = $event->getForm();

            return $form->getData();
        }
    } */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TravelTranslation::class,
            'allow_extra_fields' => true,
        ]);
    }
}
