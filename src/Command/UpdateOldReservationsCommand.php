<?php
// src/Command/UpdateOldReservationsCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OldreservationsRepository;

#[AsCommand(name: 'app:update-old-reservations',
    description: 'Corrects old reservations.',
    hidden: false,
    aliases: ['app:update-old-reservations']
)]
class UpdateOldReservationsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-old-reservations';

    public function __construct(
      private EntityManagerInterface $entityManager, 
      private OldReservationsRepository $oldReservationsRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->oldReservationsRepository = $oldReservationsRepository;
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Updates the old reservations.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to update old reservations...');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $replacements = [
            'Ãº' => 'ú',
            'Ã©' => 'é',
            'Ã³' => 'ó',
            'Â«' => '«',
            'Â»' => '»',
            'Â¡' => '¡',
            'Ã¡' => 'á',
            'Ã±' => 'ñ',
            'â‚¬' => '€',
            'í¯' => 'ï',
            'â€‹' => '',
            'Ã'  => 'í',

        ];

        foreach ($replacements as $find => $replace) {
          $this->_updateReservations($find, $replace);
        }

        $this->entityManager->flush();

        $output->writeln('Old reservations updated!');

        return Command::SUCCESS;
    }

    private function _updateReservations(string $find, string $replace): void {
      $oldReservations = $this->oldReservationsRepository->findCorruptedCharacters($find);

        foreach ($oldReservations as $oldReservation) {
            $oldReservation->setLog(str_replace($find, $replace, $oldReservation->getLog()));
            $oldReservation->setCommentaire(str_replace($find, $replace, $oldReservation->getCommentaire()));
        }
    }
}
