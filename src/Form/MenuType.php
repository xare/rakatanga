<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\MenuLocation;
use App\Entity\Pages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $langs = ['en'];
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Custom' => 'custom',
                    'Page' => 'page',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('title')
            ->add('route_name')
            ->add('position')
            ->add('visibility')
            ->add('page', EntityType::class, [
                    'class' => Pages::class,
                    'choice_label' => 'default_slug',
                    'placeholder' => 'Elige una opción',
                ])
            ->add('menuLocations', EntityType::class, [
                    'class' => MenuLocation::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Elige una Localización',
                    'expanded' => true,
                    'multiple' => true,
                ])
            ->add('menuTranslations', CollectionType::class, [
                'entry_type' => MenuTranslationType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true,
            ])
       /*  foreach($langs as $lang){
            $builder
                ->add('menuTranslations', CollectionType::class,[
                    'entry_type'=> MenuTranslationType::class,
                    'entry_options' =>[
                        'label' => false
                    ],
                ]);
        } */
           /*  ->add('menuTranslations', MenuTranslationType::class,
                                            ['data_class' => null]
                                            ) */
        /* $builder->add('Save', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ]) */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
            'allow_extra_fields' => true,
        ]);
    }
}
