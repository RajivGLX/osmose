<?php

namespace App\Manager;

use App\Dto\CenterDTO;
use App\Entity\Center;
use App\Entity\DaySlot;
use App\Entity\Region;
use App\Repository\RegionRepository;
use App\Repository\SlotsRepository;
use App\Services\Identifier;
use App\Services\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CenterManager
{
    public function __construct(
        private LoggerInterface $logger,
        private Identifier $identifier,
        private ValidatorInterface $validator,
        private Tools $tools,
        private EntityManagerInterface $entityManager,
        private RegionRepository $regionRepository,
        private SlotsRepository $slotsRepository
    ) {}

    //ANCIENNE VERSION
    // public function saveCenter(FormInterface $form, Center $center = null,Region $region =null): bool
    // {
    //     $this->entityManager->beginTransaction();
    //     try {
    //         if ($center === null) {
    //             $center = new Center();
    //         }
    //         if ($region === null) {
    //             $region = $form->get('region')->getData();
    //         }

    //         $center = $form->getData();
    //         $center->setRegion($region);
    //         $center->setDeleted(false);

    //         $this->entityManager->persist($center);
    //         $this->entityManager->flush();
    //         $this->logger->info('Création / Modification d\'un centre : ' . $center->getId());

    //         return true;
    //     } catch (\Exception $e) {
    //         $this->logger->error('Probléme lors de la sauvegarde du centre : ' . $e->getMessage());
    //         return false;
    //     }
    // }

    // public function initializeDaySlots(Center $center): Center
    // {
    //     if ($center->getDaySlots()->isEmpty()) {
    //         $slots = $this->slotsRepository->findAll();
    //         foreach ($slots as $slot) {
    //             $daySlot = new DaySlot();
    //             $center->addDaySlot($daySlot);
    //         }
    //     }
    //     return $center;
    // }

    public function deleteCenter(Center $center): Center
    {
        if ($center->isDeleted()) {
            $center->setDeleted(false);
        } else {
            $center->setDeleted(true);
        }
        $this->entityManager->persist($center);

        return $center;
    }

    public function changeActiveCenter(Center $center): Center
    {
        if ($center->isActive()) {
            $center->setActive(false);
        } else {
            $center->setActive(true);
        }
        $this->entityManager->persist($center);

        return $center;
    }

    public function createCenter(CenterDTO $centerDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($centerDTO));
            if ($validationResult) return $validationResult;

            $center = new Center();
            $center->setName($centerDTO->name);
            $center->setEmail($centerDTO->email);
            $center->setPhone($centerDTO->phone);
            $center->setUrl($centerDTO->url);
            $center->setPlaceAvailable($centerDTO->place_available);
            $center->setBand($centerDTO->band);
            $center->setLatitudeLongitude($centerDTO->latitude_longitude);
            $center->setInformation($centerDTO->information);
            $center->setRegion($this->regionRepository->find($centerDTO->region_id));
            $center->setActive($centerDTO->active);
            $center->setDeleted(false);

            $center->setAddress($centerDTO->address);
            $center->setCity($centerDTO->city);
            $center->setZipcode($centerDTO->zipcode);
            $center->setDifferentFacturation($centerDTO->different_facturation);
            $center->setAddressFacturation($centerDTO->address_facturation);
            $center->setCityFacturation($centerDTO->city_facturation);
            $center->setZipcodeFacturation($centerDTO->zipcode_facturation);

            $center->setCenterDay($centerDTO->center_day);

            $this->entityManager->persist($center);
            $this->entityManager->flush();
            $this->logger->info('Modification du centre : ' . $center->getId());

            return ['message' => 'La création du centre a bien été enregistré', 'data' => $center, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la création du centre : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la création du centre', 'data' => null, 'code' => 500];
        }
    }

    public function updateInfoCenter(CenterDTO $centerDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($centerDTO));
            if ($validationResult) return $validationResult;
            
            $center = $this->entityManager->getRepository(Center::class)->find($centerDTO->id);
            $center->setName($centerDTO->name);
            $center->setEmail($centerDTO->email);
            $center->setPhone($centerDTO->phone);
            $center->setUrl($centerDTO->url);
            $center->setPlaceAvailable($centerDTO->place_available);
            $center->setBand($centerDTO->band);
            $center->setLatitudeLongitude($centerDTO->latitude_longitude);
            $center->setInformation($centerDTO->information);
            $center->setRegion($this->regionRepository->find($centerDTO->region_id));

            $this->entityManager->persist($center);
            $this->entityManager->flush();
            $this->logger->info('Modification du centre : ' . $center->getId());

            return ['message' => 'Les information du centre a bien été modifié', 'data' => $center, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde du centre : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la sauvegarde du centre', 'data' => null, 'code' => 500];
        }
    }

    public function updateAddressCenter(CenterDTO $centerDTO): array
    {
        $validationResult = $this->tools->handleValidationErrors($this->validator->validate($centerDTO));
        if ($validationResult) return $validationResult;

        try {
            $center = $this->entityManager->getRepository(Center::class)->find($centerDTO->id);
            $center->setAddress($centerDTO->address);
            $center->setCity($centerDTO->city);
            $center->setZipcode($centerDTO->zipcode);
            $center->setDifferentFacturation($centerDTO->different_facturation);
            $center->setAddressFacturation($centerDTO->address_facturation);
            $center->setCityFacturation($centerDTO->city_facturation);
            $center->setZipcodeFacturation($centerDTO->zipcode_facturation);

            $this->entityManager->persist($center);
            $this->entityManager->flush();
            $this->logger->info('Modification du centre : ' . $center->getId());

            return ['message' => 'L\'adresse du centre a bien été modifié', 'data' => $center, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde du centre : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la sauvegarde du centre', 'data' => null, 'code' => 500];
        }
    }

    public function updateCenterDay(CenterDTO $centerDTO): array
    {
        $validationResult = $this->tools->handleValidationErrors($this->validator->validate($centerDTO));
        if ($validationResult) return $validationResult;

        try {
            $center = $this->entityManager->getRepository(Center::class)->find($centerDTO->id);
            $center->setCenterDay($centerDTO->center_day);

            $this->entityManager->persist($center);
            $this->entityManager->flush();
            $this->logger->info('Modification des CenterDay du centre : ' . $center->getId());

            return ['message' =>'Les créneaux du centre ont bien été modifiés', 'data' => $center, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde des CenterDay du centre : ' . $e->getMessage());
            return ['message' =>'Probléme lors de la sauvegarde des créneaux du centre', 'data'=> null,'code' => 500];
        }
    }

    public function changeStatusCenter($user, CenterDTO $centerDTO): array
    {
        if (!$this->identifier->isAdminDialyzone($user)) {
            return ['message' => 'Vous n\'avez pas les droits pour effectuer cette action', 'code' => 403];
        }

        $validationResult = $this->tools->handleValidationErrors($this->validator->validate($centerDTO));
        if ($validationResult) return $validationResult;

        try {
            $center = $this->entityManager->getRepository(Center::class)->find($centerDTO->id);
            $center->setActive($centerDTO->active);

            $this->entityManager->persist($center);
            $this->entityManager->flush();
            $this->logger->info('Modification du status du centre : ' . $center->getId());

            return ['message' => 'Le status du centre a bien été modifié', 'data' => $center,'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde du status du centre : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la sauvegarde du status du centre','data' => null, 'code' => 400];
        }
    }
}
