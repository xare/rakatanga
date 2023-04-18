<?php

namespace App\Form;

use App\Entity\Invoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('due_ammount')
            ->add('name')
            ->add('nif')
            ->add('address')
            ->add('postalcode')
            ->add('city')
            ->add('country', CountryType::class,[
                'preferred_choices' => [
                        'España' => 'ES',
                        'Francia' => 'FR',
                        'Reino Unido' => 'GB',
                        'Portugal' => 'PT',
                    ]
                ])
            /* ->add('reservation') */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invoices::class,
        ]);
    }
}
