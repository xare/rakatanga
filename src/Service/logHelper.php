<?php 

namespace App\Service;

use App\Entity\Dates;
use App\Entity\Logs;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

class logHelper
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function logThis(string $action, string $content, array $data, string $entity ) : bool
    {
        $log = new Logs();
            $log->setAction($action);
            $log->setContent($content);
            $log->setData($data);
            $log->setEntity($entity);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        return true;
    }

    public function logNewUser($user){
        $this->logThis(
            'Nuevo usuario ', 
            'Un nuevo usuario ha sido creado.'.$user->getEmail(), 
            [
                'Email' => $user->getEmail(),
                'Name' => $user->getPrenom(),
                'Surname' => $user->getNom(),
                'Phone' => $user->getTelephone(),
            ], 
            'user');
        return true;
    }

    public function logReservationInitialize(Dates $date, Reservation $reservation){
        $this->logThis(
            'Reservation Initialized', 
            'Reservation for <strong>'. $date->getTravel()->getMainTitle() . '</strong> travel on date <strong>' . $date->getDebut()->format('d-m-Y') . '</strong> has been initialized.', 
            [
                'id' => $reservation->getId(),
                'Viaje' => $reservation->getDate()->getTravel()->getMainTitle(),
                'Comienzo' => $reservation->getDate()->getDebut()->format('d-m-Y')
            ], 
            'reservation'
        );
        return true;
    }
}