<?php

namespace App\Services;


use App\Entity\Center;
use App\Entity\Patient;
use App\Repository\CenterRepository;
use Symfony\Component\Security\Core\User\UserInterface;

Class Identifier
{
    private $centerRepository;

    public function __construct(CenterRepository $centerRepository){
        $this->centerRepository = $centerRepository;
    }
    
    public function isAdminDialyzone(UserInterface $user = null): bool
    {
        if ($user == null){
            return false;
        }
        $roleUser = $user->getRoles();
        $adminDialyzone = 'ROLE_ADMIN_DIALYZONE';
        if (in_array($adminDialyzone, $roleUser)) {
            return true;
        }else{
            return false;
        }
    }

    public function isCenterBelongAdmin(UserInterface $user = null, Center $center): bool
    {
        if ($user == null){
            return false;
        }elseif ($this->isAdminDialyzone($user)){
            return true;
        }else{
            $centerForAdmin = $this->centerRepository->findCenterForAdmin($user->getAdministrator()->getId());
            $centerBelongsToAdmin = false;
            foreach ($centerForAdmin as $adminCenter) {
                if ($adminCenter->getId() === $center->getId()) {
                    return true;
                }
            }

            return $centerBelongsToAdmin;
        }
    }

    public function notationByPatient(Patient $patient): float
    {
        $properties = ['getPhone','getCenter', 'getRenalFailure', 'getTypeDialysis', 'isDrugAllergies', 'getDialysisStartDate', 'getVascularAccessType'];
        $notation = 0;

        foreach ($properties as $property) {
            $notation += ($patient->$property() !== null) ? 1 : 0;
        }

        return $notation;
    }

    public function isPatientHasMinInfo(Patient $patient): bool
    {
        $patientHasPhone = $patient->getPhone();
        $patientHasTypeDialysis = $patient->getTypeDialysis();

        if ($patientHasPhone === null || $patientHasTypeDialysis === null) {
            return false;
        }else{
            return true;
        }
    }
}