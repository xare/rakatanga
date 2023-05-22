<?php

namespace App\Command;

use App\Repository\InscriptionsRepository;
use App\Repository\OldreservationsRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:add-DatePaiement-to-oldreservations',
    description: 'Add Date Paiement to old reservations.',
    hidden: false,
    aliases: ['app:add-DatePaiement-to-oldreservations']
)]
class addDatePaiementToOldReservations extends Command
{
  protected static $defaultName = 'app:add-DatePaiement-to-oldreservations';
  public function __construct(
    private OldreservationsRepository $oldreservationsRepository,
    private InscriptionsRepository $inscriptionsRepository,
    private EntityManagerInterface $entityManager,
    private Connection $connection
  ) {
    $this->connection = $connection;
    parent::__construct();
  }
  public function execute(
    InputInterface $inputInterface,
    OutputInterface $outputInterface,
  ): int {
    $sql = "SELECT * FROM temp_oldreservations WHERE date_paiement_2 <> '0000-00-00 00:00:00'";
    $stmt = $this->connection->executeQuery($sql);


        while ($row = $stmt->fetchAssociative()) {
            // Do something with the row
            $dateString = $row['date_ajout'];
            $dateTimeObject = new \DateTime($dateString);
            $oldReservation = $this->oldreservationsRepository->findOneBy(['date_ajout'=> $dateTimeObject]);
          
            $str = '';
            $outputInterface->writeln($oldReservation->getCode());
            $outputInterface->writeln(substr($oldReservation->getCode(),0,4));
            if($oldReservation->getTravel() != null && $row['id'] == substr($oldReservation->getCode(),0,4)) {
              /* $str .= $row['id'].'-';
              $str .= strtoupper(substr($oldReservation->getTravel()->getCategory(),0,3)); */
              $date_paiement_2 = $row['date_paiement_2'];
              $dateTimePaiementObject = new \DateTime($date_paiement_2);
              $outputInterface->writeln($date_paiement_2);
              $oldReservation->setDatePaiement1($dateTimePaiementObject);
              $this->entityManager->flush();
              $outputInterface->writeln($date_paiement_2);
            }
        }

        return Command::SUCCESS;
  }
}