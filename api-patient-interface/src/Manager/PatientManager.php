<?php

namespace App\Manager;

use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Form\FormInterface;

class PatientManager
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}


    public function savePatient(FormInterface $form, User $user, Patient $patient = null): bool
    {
        try {
            //TODO : verifier l'utilité de cette condition à la fin
            //            if ($patient == null){
            ////                $patient = new Patient();
            ////            }

            $dataPatient = $form->get('patient')->getData();

            $patient->setPhone($dataPatient->getPhone());
            $patient->setMedicalHistory($dataPatient->getMedicalHistory());
            $patient->setCenter($dataPatient->getCenter());
            $patient->setChecked($dataPatient->isChecked());
            $patient->setDialysisStartDate($dataPatient->getDialysisStartDate());
            $patient->setTypeDialysis($dataPatient->getTypeDialysis());
            $patient->setVascularAccessType($dataPatient->getVascularAccessType());
            $patient->setDrugAllergies($dataPatient->isDrugAllergies());

            if ($dataPatient->isDrugAllergies() === true) {
                $patient->setDrugAllergiePrecise($dataPatient->getDrugAllergiePrecise());
            } else {
                $patient->setDrugAllergiePrecise($dataPatient->isDrugAllergies());
            }

            $patient->setUser($user);
            $this->entityManager->persist($patient);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde du patient : ' . $e->getMessage());
            return false;
        }
    }

    public function updatePatient(FormInterface $form, Patient $patient): bool{
        try {
            $data = $form->getData();
            $patient->setPhone($data->getPhone());
            $patient->setTypeDialysis($data->getTypeDialysis());

            $this->entityManager->persist($patient);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Mise à jour du patient : ' . $e->getMessage());
            return false;
        }
    }
}
