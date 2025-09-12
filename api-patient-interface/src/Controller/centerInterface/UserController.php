<?php

namespace App\Controller\centerInterface;

use App\Dto\PatientDTO;
use App\Dto\UserDTO;
use App\Manager\UserManager;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Services\Identifier;
use App\Services\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private UserManager $userManager,
        private UserDTO $userDTO,
        private PatientDTO $patientDTO,
        private Tools $tools,
        private LoggerInterface $logger,
        private Identifier $identifier,
    ) {}

    #[Route('/api/user-connect', name: 'app_user_connected')]
    public function userConnected(): Response
    {
        return $this->json($this->getUser(), 200, [], ['groups' => 'info_user']);
    }


    #[Route('/api/get-all-admins', name: 'api_all_admins', methods: ['GET'])]
    public function getAllAdmins(): Response
    {
        if ($this->identifier->isadminOsmose($this->getUser())) {
            $allAdmins = $this->userRepository->getAllAdmins();
            return $this->json(['data' => $allAdmins, 'message' => 'Chargement des administrateurs reussis'], 200, [], ['groups' => 'info_user']);
        } else {
            return $this->json(['message' => 'Vous n\'avez pas les droits pour accéder à cette page'], 403);
        }
    }


    #[Route('/api/get-one-patient', name: 'api_get_patient', methods: ['GET'])]
    public function getOnePatient(): Response
    {
        $patient = $this->userRepository->findOneBy(['id' => 23]);
        return $this->json($patient, 200, [], ['groups' => 'info_user']);
    }


    #[Route('/api/create-admin', name: 'api_create_admin', methods: ['POST'])]
    public function createAdmin(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['lastname', 'firstname', 'role_array', 'center_array', 'email'];
        $userDTO = $this->tools->requiredFields($requiredFields, $data, $this->userDTO);

        if (is_array($userDTO) || !empty($userDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la création de l\'admin : ' . json_encode($userDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire', 'data' => null], 400);
        }

        $result = $this->userManager->createAdmin($userDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_user']);
        
    }


    #[Route('/api/update-info-admin', name: 'api_update_info_admin', methods: ['POST'])]
    public function updateInfoAdmin(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'lastname', 'firstname', 'role_array','center_array'];
        $userDTO = $this->tools->requiredFields($requiredFields, $data, $this->userDTO);
        if (is_array($userDTO) || !empty($userDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation des informations de l\'admin : ' . json_encode($userDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire', 'data' => null], 400);
        }

        if($data['new_password'] != null){
            $userDTO->current_password = $data['current_password'];
            $userDTO->new_password = $data['new_password'];
        }
        if ($data['email'] != null) {
            $userDTO->email = $data['email'];
        }

        $result = $this->userManager->updateInfoAdmin($userDTO);
        $reload = array_key_exists('reload', $result) ? $result['reload'] : false;

        return $this->json(['message' => $result['message'], 'data' => $result['data'], 'reload' => $reload], $result['code'], [], ['groups' => 'info_user']);
    }


    #[Route('/api/update-info-patient', name: 'api_update_info_patient', methods: ['POST'])]
    public function updateInfoPatient(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['idUser', 'lastname', 'firstname', 'email', 'checked'];
        $otherFields = [
            'center','phone', 'type_dialysis', 'medical_history', 'dialysis_start_date', 
            'vascular_access_type','drug_allergies', 'drug_allergie_precise'
        ];
        $patientDTO = $this->tools->requiredFields($requiredFields, $data, $this->patientDTO);
        $patientDTO = $this->tools->otherFields($otherFields, $data, $patientDTO);

        if (is_array($patientDTO) || !empty($patientDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation des informations du patient : ' . json_encode($patientDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire', 'data' => null], 400);
        }

        $result = $this->userManager->updateInfoPatient($patientDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_user']);
    }

    #[Route('/api/create-patient', name: 'api_create_patient', methods: ['POST'])]
    public function createPatient(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['lastname', 'firstname', 'email', 'checked'];
        $otherFields = [
            'center', 'phone', 'type_dialysis', 'medical_history', 'dialysis_start_date', 
            'vascular_access_type', 'drug_allergies', 'drug_allergie_precise'
        ];
        $patientDTO = $this->tools->requiredFields($requiredFields, $data, $this->patientDTO);
        $patientDTO = $this->tools->otherFields($otherFields, $data, $patientDTO);

        if (is_array($patientDTO) || !empty($patientDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la création des informations du patient : ' . json_encode($patientDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire', 'data' => null], 400);
        }

        $result = $this->userManager->createPatient($patientDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_user']);
    }


    #[Route('/api/get-all-patients', name: 'api_all_patients', methods: ['GET'])]
    public function getAllPatients(): Response
    {
        if ($this->identifier->isadminOsmose($this->getUser())) {
            $allPatients = $this->userRepository->getAllPatients();
            return $this->json(['data' => $allPatients, 'message' => 'Chargement des patients reussis'], 200, [], ['groups' => 'info_user']);
        } else {
            return $this->json(['message' => 'Vous n\'avez pas les droits pour accéder à cette page'], 403);
        }
    }


    #[Route('/api/user-change-status', name: 'api_update_user_status', methods: ['POST'])]
    public function updateUserStatus(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'valid'];
        $userDTO = $this->tools->requiredFields($requiredFields, $data, $this->userDTO);
        if (!empty($userDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation du status du user : ' . json_encode($userDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        }
        $result = $this->userManager->changeStatusUser($this->getUser(), $userDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_user']);
    }

}
