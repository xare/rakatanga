<?php

namespace App\Form;

use Adamski\Symfony\PhoneNumberBundle\Form\PhoneNumberType;
use App\Entity\Contact;
use App\Entity\TravelTranslation;
use App\Repository\LangRepository;
use App\Repository\TravelTranslationRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    private $travelTranslationRepository;
    private $langRepository;

    public function __construct(
        TravelTranslationRepository $travelTranslationRepository,
        LangRepository $langRepository){
        $this->travelTranslationRepository = $travelTranslationRepository;
        $this->langRepository = $langRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $travelTranslations = $this
                                ->travelTranslationRepository
                                ->findBy([
                                    'lang' => $options['lang']
                                ]);
        
        $choices = $travelTranslations;
        
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
           // ->add('phone', TextType::class)
            //->add('country', CountryType::class)
            ->add('travel', EntityType::class, [
                    'class' => TravelTranslation::class,
                    'placeholder'=> "Elige los viajes que más te interesan",
                    'choices' => $choices,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => false
                    ])
            ->add('info', ChoiceType::class,[
                'choices' => ["Información general" => "Información general"],
                'multiple' => true,
                'expanded' => true,
                'mapped' => false
            ])
            ->add('phone', 
                    PhoneNumberType::class,
                    [
                        "label"     => "Phone number",
                        "preferred" => ["ES","FR","GB","BE","PT","NL","BE","IT","GR","TR","IN","LK","AR"],
                        "required"  => false
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
            'lang' => []
        ]);
    }
}
