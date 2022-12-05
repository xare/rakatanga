<?php

namespace App\Form;

use App\Entity\ReservationData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationDataType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('passportNo', null, [
                'icon_before' => '<i class="fa fa-passport"></i>',
                'label' => $this->translator->trans('Número del pasaporte'),
            ])
            ->add('passportIssueDate', null, [
                'label' => $this->translator->trans('Fecha de expedición del pasaporte'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('passportExpirationDate', null, [
                'label' => $this->translator->trans('Fecha de caducidad del pasaporte'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('visaNumber', null, [
                                        'label' => $this->translator->trans('Número del visado'),
                                    ])
            ->add('visaIssueDate', null, [
                'label' => $this->translator->trans('Fecha de emisión del visado'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('visaExpirationDate', null, [
                'label' => $this->translator->trans('Fecha de caducidad del visado'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('driversNumber', null, [
                            'label' => $this->translator->trans('Nº del carnet de conducir'),
                            ])
            ->add('driversIssueDate', null, [
                'label' => $this->translator->trans('Fecha de emisión del carnet de conducir'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('driversExpirationDate', null, [
                'label' => $this->translator->trans('Fecha de caducidad del carnet de conducir'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('insuranceCompany', null, [
                'label' => $this->translator->trans('Compañía de seguros'),
                ])
            ->add('insuranceContractNumber', null, [
                'label' => $this->translator->trans('Nº del contrato del seguro'),
                ])
            ->add('abroadPhoneNumber', null, [
                'label' => $this->translator->trans('Número de teléfono de contacto'),
                ])
            ->add('contactPersonName', null, [
                'label' => $this->translator->trans('Nombre de la persona de contacto'),
                ])
            ->add('contactPersonPhone', null, [
                'label' => $this->translator->trans('Número de teléfono de tu contacto'),
                ])
            ->add('flightNumber', null, [
                'label' => $this->translator->trans('Nº de vuelo de ida'),
                ])
            ->add('flightArrival', null, [
                'label' => $this->translator->trans('Fecha de llegada'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('flightArrivalAirport', null, [
                'label' => $this->translator->trans('Aeropuerto de llegada'),
                ])
            ->add('ArrivalHotel', null, [
                'label' => $this->translator->trans('Hotel en el destino'),
                ])
            ->add('flightDepartureNumber', null, [
                'label' => $this->translator->trans('Nº de vuelo de vuelta'),
                ])
            ->add('flightDeparture', null, [
                'label' => $this->translator->trans('Fecha de salida'),
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('flightDepartureAirport', null, [
                'label' => $this->translator->trans('Aeropuerto de salida'),
                ])
            ->add('DepartureHotel', null, [
                'label' => $this->translator->trans('Hotel antes del regreso'),
                ])
            /* ->add('reservation')
            ->add('documents')
            ->add('User')
            ->add('traveller') */

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationData::class,
        ]);
    }
}
