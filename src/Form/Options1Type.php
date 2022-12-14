<?php

namespace App\Form;

use App\Entity\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Options1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /* ->add('title')
            ->add('intro') */
            ->add('price')
            ->add('travel')
            ->add('isExtension')
            ->add('optionsTranslations', CollectionType::class, [
                'entry_type' => OptionsTranslationsType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Options::class,
            'allow_extra_fields' => true,
        ]);
    }
}
