<?php

namespace App\Form;

use App\Entity\Mailings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject')
            ->add('content')
            ->add('ToAddresses')
            ->add('attachment')
            ->add('category', ChoiceType::class,[
                'choices' => [
                    'reservation' => 'reservation',
                    'user' => 'user',
                    'contact' => 'contact'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mailings::class,
        ]);
    }
}
