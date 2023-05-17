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

#[AsCommand(name: 'app:add-code-to-oldreservations',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-code-to-oldreservations']
)]
class addCodeToOldReservations extends Command
{
  protected static $defaultName = 'app:add-code-to-oldreservations';
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
    $sql = 'SELECT * FROM temp_oldreservations';
    $stmt = $this->connection->executeQuery($sql);


        while ($row = $stmt->fetchAssociative()) {
            // Do something with the row
            $dateString = $row['date_ajout'];
            $dateTimeObject = new \DateTime($dateString);
            $oldReservation = $this->oldreservationsRepository->findOneBy(['date_ajout'=> $dateTimeObject]);
          
            $str = '';
            if($oldReservation->getTravel() != null) {
              $str .= $row['id'].'-';
              $str .= strtoupper(substr($oldReservation->getTravel()->getCategory(),0,3));
              
              $oldReservation->setCode($str);
              $this->entityManager->flush();
            }
            $outputInterface->writeln($str);
        }

        return Command::SUCCESS;
  }
}