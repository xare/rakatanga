<?php

namespace App\Form;

use Adamski\Symfony\PhoneNumberBundle\Form\PhoneNumberType;
use App\Entity\Contact;
use App\Entity\TravelTranslation;
use App\Repository\LangRepository;
use App\Repository\TravelTranslationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactType extends AbstractType
{
    private TravelTranslationRepository $travelTranslationRepository;
    private LangRepository $langRepository;
    private TranslatorInterface $translator;

    public function __construct(
        TravelTranslationRepository $travelTranslationRepository,
        LangRepository $langRepository,
        TranslatorInterface $translator
        ) {
        $this->travelTranslationRepository = $travelTranslationRepository;
        $this->langRepository = $langRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $travelTranslations = $this
                                ->travelTranslationRepository
                                ->findBy([
                                    'lang' => $options['lang'],
                                ]);

        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('travel', EntityType::class, [
                    'class' => TravelTranslation::class,
                    'placeholder' => $this->translator->trans('Elige los viajes que más te interesan'),
                    'choices' => $travelTranslations,
                    'multiple' => true,
                    'expanded' => false,
                    'mapped' => false,
                    ])
            ->add('info', ChoiceType::class, [
                'choices' => [$this->translator->trans('Información general') => $this->translator->trans('Información general')],
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('phone',
                PhoneNumberType::class,
                [
                    'label' => 'Phone number',
                    'preferred' => ['ES', 'FR', 'GB', 'BE', 'PT', 'NL', 'BE', 'IT', 'GR', 'TR', 'IN', 'LK', 'AR'],
                    'required' => false,
                ])
            ->add('email', EmailType::class)
            ->add('message', TextareaType::class)
            ->add('send', SubmitType::class)
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                if ($event->getData() === 'Información general') {
                    $event->setData(null);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'lang' => [],
        ]);
    }
}
