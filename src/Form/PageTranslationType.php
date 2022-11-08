<?php

namespace App\Form;

use App\Entity\PageTranslation;
use App\Entity\Lang;
use App\Entity\Menu;
use App\Entity\Pages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class PageTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('slug')
            ->add('intro')
            ->add('body', CKEditorType::class)
            ->add('lang', EntityType::class,[
                'class' => Lang::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageTranslation::class,
        ]);
    }
}
