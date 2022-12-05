<?php

namespace App\Command;

use App\Repository\DatesRepository;
use App\Repository\InscriptionsRepository;
use App\Repository\OldreservationsRepository;
use App\Repository\TravelTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:oldreservation-indexer',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:oldreservation-indexer']
)]
class OldReservationsIndexerCommand extends Command
{
    protected static $defaultName = 'app:oldreservation-indexer';

    public function __construct(
        private OldreservationsRepository $oldreservationsRepository,
        private InscriptionsRepository $inscriptionsRepository,
        private TravelTranslationRepository $travelTranslationRepository,
        private DatesRepository $datesRepository,
        private EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->oldreservationsRepository = $oldreservationsRepository;
        $this->inscriptionsRepository = $inscriptionsRepository;
        $this->travelTranslationRepository = $travelTranslationRepository;
        $this->datesRepository = $datesRepository;
        $this->entityManager = $entityManager;
    }

    public function execute(
    InputInterface $inputInterface,
    OutputInterface $outputInterface,
    ): int {
        // Browse all of the OldReservationsRepository
        $oldReservations = $this->oldreservationsRepository->findAll();
        $i = 0;
        $data = [];
        foreach ($oldReservations as $oldReservation) {
            echo 'Old Reservation ID: '.$oldReservation->getId();
            $data[$i]['status'] = 'To be processed';
            $matches = [];
            $string = $oldReservation->getLog();
            if ($string !== '') {
                if ($oldReservation->getInscriptions() == null) {
                    $pattern = "/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i";
                    preg_match($pattern, $string, $matches);
                    if (!empty($matches)) {
                        $inscriptionsByOldreservation = $this->inscriptionsRepository->findby(['email' => $matches[0]]);
                        if (null !== $inscriptionsByOldreservation) {
                            foreach ($inscriptionsByOldreservation as $inscriptionByOldreservation) {
                                $data[$i]['status'] = 'Inscription OK';
                                $data[$i]['status']['isInscription'] = true;

                                $oldReservation->setInscriptions($inscriptionByOldreservation);
                                $this->entityManager->persist($oldReservation);
                                $this->entityManager->flush();
                            }
                        }
                    }
                }
                // Travel search
                $data[$i]['travel'] = [];
                $data[$i]['travel']['status'] = 'Travel Not OK';
                $data[$i]['travel']['isTravel'] = false;
                if ($oldReservation->getTravel() == null) {
                    echo 'This reservation has not assigned a travel.';
                    $patternTravel = "/(Voyage|Viaje|Travel)\s?:\s[a-zàéíúóáñA-Z_\.\,\s\-\ \'\:]*/";
                    preg_match($patternTravel, $string, $matchesTravel);
                    if (!empty($matchesTravel)) {
                        $pregMatchResult = strrpos(trim($matchesTravel[0]), '.', -1) != 0 ? substr(trim($matchesTravel[0]), 0, -1) : trim($matchesTravel[0]);

                        $data[$i]['travel']['match'] = $pattern;
                        $data[$i]['travel']['preg_replace'] = preg_replace(
                            '/\s\s+/',
                            ' ',
                            str_replace(
                                ['Voyage : ', 'Viaje: ', 'Travel : '],
                                ['', '', ''],
                                $pregMatchResult));
                        $travelTranslations = $this->travelTranslationRepository->findBy(
                            ['title' => preg_replace(
                                '/\s\s+/',
                                ' ',
                                str_replace(
                                    ['Voyage : ', 'Viaje: ', 'Travel : '],
                                    ['', '', ''],
                                    $pregMatchResult))]);
                        $data[$i]['travel']['travelTranslations'] = $travelTranslations;
                        $j = 0;
                        foreach ($travelTranslations as $travelTranslation) {
                            $travel = $travelTranslation->getTravel();
                            $data[$i]['travel'][$j]['travelTranslation']['title'] = $travel->getMainTitle();
                            $data[$i]['travel'][$j]['travelTranslation']['status'] = 'Travel OK';
                            $data[$i]['travel'][$j]['travelTranslation']['isTravel'] = true;
                            $oldReservation->setTravel($travel);
                            $this->entityManager->persist($oldReservation);
                            $this->entityManager->flush();
                            ++$j;
                        }
                    }
                }
                // Date Search

                if ($data[$i]['travel']['isTravel'] == true) {
                    echo 'Dump Travel : ';

                    echo 'Dump Dates';

                    if ($oldReservation->getDates() == null) {
                        $patternDate = "/(Dates|Fechas)\s?:\s(du|del|from)\s\d{1,2}\s(\w+\s)?(au|al|to)\s\d{1,2}\s\w+\s\d{4}/u";
                        preg_match($patternDate, $string, $matchesDate);
                        if (!empty($matchesDate)) {
                            $data[$i]['matchesDate'] = $matchesDate[0];
                            $dateString = str_replace(
                                ['Dates : du ', 'Fechas: del ', 'Dates: from '],
                                ['', '', ''],
                                $matchesDate[0]
                            );
                            $dateString = str_replace(
                                ['au', 'al', 'to'],
                                ['', '', ''],
                                $dateString
                            );

                            $data[$i]['datestring'] = $dateString;
                            $dateArray = explode(' ', $dateString);
                            $data[$i]['dateArray'] = $dateArray;

                            if (count($dateArray) == '5') {
                                // same month
                                // DateDebut
                                $monthNumberDebut = date_parse($dateArray[3]);
                                $monthsTranslations = [
                                    'janvier' => '01',
                                    'février' => '02',
                                    'mars' => '03',
                                    'avril' => '04',
                                    'mai' => '05',
                                    'juin' => '06',
                                    'juillet' => '07',
                                    'aout' => '08',
                                    'septembre' => '09',
                                    'octobre' => '10',
                                    'novembre' => '11',
                                    'décembre' => '12',
                                    'enero' => '01',
                                    'febrero' => '02',
                                    'marzo' => '03',
                                    'abril' => '04',
                                    'mayo' => '05',
                                    'junio' => '06',
                                    'julio' => '07',
                                    'agosto' => '08',
                                    'septiembre' => '09',
                                    'octubre' => '10',
                                    'noviembre' => '11',
                                    'diciembre' => '12',
                                    'january' => '01',
                                    'february' => '02',
                                    'mars' => '03',
                                    'april' => '04',
                                    'may' => '05',
                                    'june' => '06',
                                    'july' => '07',
                                    'august' => '08',
                                    'september' => '09',
                                    'october' => '10',
                                    'november' => '11',
                                    'december' => '12',
                                ];
                                if ($monthsTranslations[$dateArray[3]] != '') {
                                    $monthNumberDebut = $monthsTranslations[$dateArray[3]];
                                }

                                $dateTime = new \DateTime();
                                $dateDebutFormatted = $dateTime::createFromFormat('Y-m-d', $dateArray[4].'-'.$monthNumberDebut.'-'.$dateArray[0]);
                                $dateFinFormatted = $dateTime::createFromFormat('Y-m-d', $dateArray[4].'-'.$monthNumberDebut.'-'.$dateArray[2]);

                                $dates = $this->datesRepository->findBy([
                                        'debut' => $dateDebutFormatted,
                                        'fin' => $dateFinFormatted,
                                        'travel' => $travel,
                                    ]);
                                $k = 0;
                                foreach ($dates as $dateItem) {
                                    $data[$i]['date'][$k]['debut'] = $dateItem->getDebut();
                                    $data[$i]['date'][$k]['isDate'] == true;
                                    $oldReservation->setDates($dateItem);
                                    $this->entityManager->persist($oldReservation);
                                    $this->entityManager->flush();
                                    ++$k;
                                }
                            }
                        }
                    }
                }
            }
            ++$i;
        }
        echo $i;
        var_dump($data);

        return Command::SUCCESS;
    }
}
