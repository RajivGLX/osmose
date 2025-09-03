<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\AdministratorRepository;
use Symfony\Component\Form\FormInterface;
use App\Entity\Administrator;
use App\Entity\User;

class AdministratorManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private AdministratorRepository $adminRepository
    ) {}

    public function saveAdmin(FormInterface $form, User $user, Administrator $admin = null): bool
    {
        try {
            if ($admin == null) {
                $admin = new Administrator();
                $centerResult = $this->saveCentersForAdmin($form, $admin);
                if (!$centerResult) return false;
            } else {
                $centerResult = $this->updateCentersForAdmin($form, $user, $admin);
                if (!$centerResult) return false;
            }

            $admin->setUser($user);

            $this->entityManager->persist($admin);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde de l\'admininstrateur : ' . $e->getMessage());
            return false;
        }
    }

    public function saveCentersForAdmin(FormInterface $form, Administrator $admin): bool
    {
        try {
            $centers = $form->get('center')->getData()->toArray();
            foreach ($centers as $center) {
                $admin->addCenter($center);
            }
            $this->entityManager->persist($admin);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Affectation des centres à l\'admin : ' . $e->getMessage());
            return false;
        }
    }

    public function updateCentersForAdmin(FormInterface $form, User $user, Administrator $admin): bool
    {
        try {
            $centers = $form->get('center')->getData()->toArray();
            $centerAssignAdminEdit = $this->adminRepository->getCentersAdmin($user);
            $centersHasAdd = array_diff($centers, $centerAssignAdminEdit);
            $centersHasDeleted = array_diff($centerAssignAdminEdit, $centers);

            foreach ($centersHasAdd as $center) {
                $admin->addCenter($center);
            }
            foreach ($centersHasDeleted as $center) {
                $admin->removeCenter($center);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Modification de l\'affectation des centres à l\'admin : ' . $e->getMessage());
            return false;
        }
    }
}
