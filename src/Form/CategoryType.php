<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Continents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('status', ChoiceType::class, [
                'placeholder'=>"Qué pongo aquí??",
                'choices' => [
                    'Yes' => 'yes',
                    'No' => 'no'
                ]
            ])
            ->add('Continents', EntityType::class, [
                'class' => Continents::class,
                'placeholder' => 'Elige Continente: ',
                'choice_label' => 'code'
            ] )
            ->add('blogs')
            ->add('categoryTranslations', CollectionType::class, [
                'entry_type' => CategoryTranslationType::class,
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
            'data_class' => Category::class,
            'allow_extra_fields' => true
        ]);
    }
}
