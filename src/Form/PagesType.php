<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Pages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('default_slug')
            ->add('date_created', DateTimeType::class ,[
                'widget' => 'single_text',
                'disabled' => true
            ])
            ->add('date_modified',  DateTimeType::class,[
                'widget' => 'single_text',
                'disabled' => true
            ])
            ->add('pageTranslations', CollectionType::class, [
                'entry_type' => PageTranslationType::class,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pages::class,
            'allow_extra_fields' => true
        ]);
    }
}
