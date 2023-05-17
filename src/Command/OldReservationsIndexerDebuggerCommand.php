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

#[AsCommand(name: 'app:oldreservation-indexe-debugger',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:oldreservation-indexer-debugger']
)]
class OldReservationsIndexerDebuggerCommand extends Command
{
    protected static $defaultName = 'app:oldreservation-indexer-debugger';
    private const ID_OFFSET = 699;

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
        $oldReservations = $this->oldreservationsRepository->findWithIdGreaterOrEqual(self::ID_OFFSET);
        $i = 0;
        //we will send the info to an array calle $data
        $data = [];
        foreach ( $oldReservations as $oldReservation ) {
            $outputInterface->writeln( 'Old Reservation ID: '.$oldReservation->getId());
            $data[$i]['status'] = 'To be Processed';
            // we fetch the log from which we can extract the data
            $log = $oldReservation->getLog();
            if ($oldReservation->getInscriptions() == null) { // no inscriptions let's add one
                $emailPattern = "/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i";
                preg_match($emailPattern, $log, $inscriptionMatches);
                if(!empty($inscriptionMatches)) {
                    $inscriptionsByOldreservation = $this->inscriptionsRepository->findby(['email' => $inscriptionMatches[0]]);
                    //if there are inscriptions which email was in the reservation's log message
                    if (null !== $inscriptionsByOldreservation) {
                        foreach ($inscriptionsByOldreservation as $inscriptionByOldreservation) {
                            $data[$i]['status'] = 'Inscription OK';
                            $data[$i]['isInscription'] = true;
                            $oldReservation->setInscriptions($inscriptionByOldreservation);
                            $this->entityManager->persist($oldReservation);
                            $this->entityManager->flush();
                        }
                    }
                }

                // Travel search
                $data[$i]['travel'] = [];
                $data[$i]['travel']['status'] = 'Travel Not OK';
                $data[$i]['travel']['isTravel'] = false;
                if ($oldReservation->getTravel() == null) { 
                    
                }
            }
            $i++;
        }

        return Command::SUCCESS;
    }
}