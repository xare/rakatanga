<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\Popups;
use App\Entity\PopupsTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PopupsTranslationType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
    ->add('title', TextType::class)
    ->add('content', TextType::class)
    ->add('lang', EntityType::class,[
        'class' => Lang::class,
        'choice_label' => 'name',
        'multiple' => false,
        'expanded' => false,
    ]);
  }

  public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PopupsTranslation::class,
            'allow_extra_fields' => true
        ]);
    }
}
