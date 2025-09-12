<?php

namespace App\Command;

use App\Manager\BookingManager;
use App\Repository\BookingRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:booking:refresh:status', 'Change status for the old booking on status finish')]
class ChangeStatus extends Command {
    private $bookingRepository;
    private $statusRepository;
    private $bookingManager;
    private $logger;

    public function __construct(
        BookingRepository $bookingRepository,
        StatusRepository $statusRepository,
        BookingManager $bookingManager,
        LoggerInterface $dbLogger
    )
    {
        parent::__construct();
        $this->bookingRepository = $bookingRepository;
        $this->statusRepository = $statusRepository;
        $this->bookingManager = $bookingManager;
        $this->logger = $dbLogger;
    }

    protected function configure()
    {
        $this
            ->setName('app:booking:refresh:status')
            ->setDescription('Change status for the old booking on status finish');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $statusFinish = $this->statusRepository->findOneBy(['status_finish' => true]);
            if (empty($statusFinish)) {
                $this->logger->error('Status finish n\'a pas été trouvé.');
                $io->error('Status finish n\'a pas été trouvé.');
                return Command::FAILURE;
            }

//            $adminOsmose = $this->userRepository->findOneBy(['roles' => ["ROLE_ADMIN_OSMOSE"]]);
            $adminOsmose = null;
            if (empty($adminOsmose)) {
                $this->logger->error('Admin Osmose n\'a pas été trouvé.');
                $io->error('Admin Osmose n\'a pas été trouvé.');
                return Command::FAILURE;
            }

            $bookings = $this->bookingRepository->findOldBookingsByStatus($statusFinish);
            if (empty($bookings)) {
                $this->logger->info('Aucune réservation à mettre à jour.');
                $io->success('Aucune réservation à mettre à jour.');
                return Command::SUCCESS;
            }

            $numberBookings = count($bookings);
            $result = $this->bookingManager->changeStatusBookingBatch($adminOsmose, $statusFinish, $bookings);
            if($result){
                $io->success(sprintf('Modification de "%d" status de reservation passé.', $numberBookings));
                $this->logger->info(sprintf('Modification de "%d" status de reservation passé.', $numberBookings));
                return Command::SUCCESS;
            }else{
                $io->error('Une erreur est survenue dans le bookingManager');
                $this->logger->error('Une erreur est survenue dans le bookingManager');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $io->error('Une erreur est survenue lors de la mise à jour des statuts des réservations : ' . $e->getMessage());
            $this->logger->error('Une erreur est survenue lors de la mise à jour des statuts des réservations : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}