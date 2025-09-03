<?php

namespace App\Manager;

use App\Entity\Availability;
use App\Entity\Center;
use App\Entity\Slots;
use App\Repository\AvailabilityRepository;
use App\Repository\SlotsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class AvailabilityManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private AvailabilityRepository $availabilityRepository,
        private SlotsRepository $slotsRepository
    ) {}

    public function saveAvailability($allAvailability, Center $center)
    {
        try {
            foreach ($allAvailability as $month) {
                foreach ($month as $day => $slots) {
                    $dateData = new \DateTime($day);
                    foreach ($slots as $slotName => $availability) {
                        // Trouver le slot par son nom
                        $slot = $this->slotsRepository->findOneBy(['name' => $slotName]) ?? throw new \Exception("Slot not found: " . $slotName);

                        // Vérifier si une disponibilité existe déjà
                        $existsAvailability = $this->availabilityRepository->existsAvailability($dateData, $slot->getName(), $center->getId());

                        $placeCount = $availability['check'] ? $availability['qty'] : 0;

                        if ($existsAvailability) {
                            // Mise à jour d'une disponibilité existante
                            $existsAvailability->setAvailablePlace($placeCount);
                            $this->entityManager->persist($existsAvailability);
                        } else {
                            // Création d'une nouvelle disponibilité
                            if (!$this->createAvailability($center, $dateData, $slot, $placeCount)) {
                                return ['message' => 'Problèmes lors de la sauvegarde des disponibilités', 'data' => null, 'code' => 400];
                            }
                        }
                    }
                }
            }

            $this->entityManager->flush();
            $this->logger->info('Les disponibilités pour le centre :' . $center->getSlug() . ' ont bien été sauvegardées');
            return ['message' => 'Mise à jour des disponibilités enregistrée', 'data' => null, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problèmes lors de la sauvegarde des disponibilités pour le centre :' . $center->getSlug() . ' : ' . $e->getMessage());
            return ['message' => 'Problèmes lors de la sauvegarde des disponibilités', 'data' => null, 'code' => 400];
        }
    }

    public function createAvailability(Center $center, \DateTime $date, Slots $slot, int $availability): bool
    {
        try {
            $availabilityEntity = new Availability();
            $availabilityEntity->setCenter($center);
            $availabilityEntity->setDate($date);
            $availabilityEntity->setAvailablePlace($availability);
            $availabilityEntity->setSlot($slot);
            $availabilityEntity->setReservedPlace(0);
            $this->entityManager->persist($availabilityEntity);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de la création de la disponibilité pour le centre ' . $center->getSlug() . ' : ' . $e->getMessage());
            return false;
        }
    }

    public function updateAvailabilityForBooking(Availability $availability): bool
    {
        try {
            $availablePlace = $availability->getAvailablePlace();
            $reservedPlace = $availability->getReservedPlace();
            $availability->setAvailablePlace($availablePlace - 1);
            $availability->setReservedPlace($reservedPlace + 1);

            $this->entityManager->persist($availability);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Problémes lors de la mise à jour de la disponibilité id:' . $availability->getId() . ' / porbléme : ' . $e->getMessage());
            return false;
        }
    }
}
