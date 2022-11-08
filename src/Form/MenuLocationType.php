<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\MenuLocation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MenuLocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('menus', EntityType::class, [
                'class' => Menu::class, 
                'choice_label' => 'title',
                'expanded' => true,
                'multiple' => true,
                'placeholder' => 'Elige una opción'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuLocation::class,
        ]);
    }
}
