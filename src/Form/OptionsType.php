<?php

namespace App\Form;

use App\Entity\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelleFr')
            ->add('libelleEs')
            ->add('libelleEn')
            ->add('libelleDe')
            ->add('libelleIt')
            ->add('introFr')
            ->add('introEs')
            ->add('introEn')
            ->add('introDe')
            ->add('introIt')
            ->add('prix')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Options::class,
        ]);
    }
}
