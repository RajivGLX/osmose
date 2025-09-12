<?php

namespace App\Manager;

use App\Dto\PatientDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Dto\UserDTO;
use App\Entity\Administrator;
use App\Entity\Patient;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Repository\AdministratorRepository;
use App\Repository\CenterRepository;
use App\Repository\UserRepository;
use App\Services\Identifier;
use App\Services\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $passwordHash,
        private Identifier $identifier,
        private Security $security,
        private AdministratorManager $adminManager,
        private PatientManager $patientManager,
        private Tools $tools,
        private ValidatorInterface $validator,
        private UserRepository $userRepository,
        private CenterRepository $centerRepository,
        private Administrator $administrator,
        private AdministratorRepository $adminRepository,
    ) {}

    public function createAdmin(UserDTO $userDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($userDTO));
            if ($validationResult) return $validationResult;

            $user = new User();
            $admin = new Administrator();
            $user->setAdministrator($admin);
            $admin->setUser($user);
            $user->setAdmin(true);
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setRoles($userDTO->role_array);
            $user->setfirstname($userDTO->firstname);
            $user->setLastname($userDTO->lastname);
            $user->setEmail($userDTO->email);

            // Générer un mot de passe aléatoire
            $randomPassword = bin2hex(random_bytes(8));
            $user->setPassword($this->passwordHash->hashPassword($user, $randomPassword));

            $this->addCentersAdmin($admin, $userDTO->center_array);


            $this->entityManager->persist($user, $admin);
            $this->entityManager->flush();
            $this->logger->info('Modification de l\'admin : ' . $user->getId());

            // TODO: envoyer un email avec le mot de passe
            // $this->sendPasswordEmail($userDTO->email, $randomPassword);

            return ['message' => 'L\'admininstrateur a bien été créé, un email avec son mot de passe lui a été envoyé', 'data' => $user,  'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la création de l\'admin : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la création de l\'admin', 'data' => null, 'code' => 500];
        }
    }

    public function updateInfoAdmin(UserDTO $userDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($userDTO));
            if ($validationResult) return $validationResult;
            $reload = false;

            $user = $this->entityManager->getRepository(user::class)->find($userDTO->id);
            $admin = $user->getAdministrator();
            $this->removeCentersAdmin($admin);
            $this->addCentersAdmin($admin, $userDTO->center_array);
            $user->setRoles($userDTO->role_array);
            $user->setfirstname($userDTO->firstname);
            $user->setLastname($userDTO->lastname);
            if($userDTO->email != null){
                $user->setEmail($userDTO->email);
                $reload = true;
            } 
            if($userDTO->new_password != null && $userDTO->current_password != null){
                if($this->passwordHash->isPasswordValid($user, $userDTO->current_password)){
                    $user->setPassword($this->passwordHash->hashPassword($user, $userDTO->new_password));
                    $reload = true;
                }else{
                    $this->logger->error('Le mot de passe actuel ne correspond pas');
                    return ['message' => 'Le mot de passe actuel ne correspond pas', 'data' => null, 'code' => 400];
                }
            } 

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->logger->info('Modification de l\'admin : ' . $user->getId());

            return ['message' => 'Les information admininstrateur ont bien été modifié', 'data' => $user, 'reload' => $reload, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde de l\'admin : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la sauvegarde de l\'admin', 'data' => null, 'code' => 500];
        }
    }

    public function createPatient(PatientDTO $patientDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($patientDTO));
            if ($validationResult) return $validationResult;

            $user = new User();
            $patient = new Patient();
            $user->setfirstname($patientDTO->firstname);
            $user->setLastname($patientDTO->lastname);
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setAdmin(false);
            $user->setRoles(['ROLE_PATIENT']);
            $user->setPatient($patient);
            // Générer un mot de passe aléatoire
            $randomPassword = bin2hex(random_bytes(8));
            $user->setPassword($this->passwordHash->hashPassword($user, $randomPassword));
            $existingUser = $this->userRepository->findOneBy(['email' => $patientDTO->email]);
            if ($existingUser) {
                throw new \Exception('L\'email existe déjà.');
                return ['message' => 'L\'email est associé a un autre compte, veuillez en choisir une autre', 'data' => null, 'code' => 500];
            }
            $user->setEmail($patientDTO->email);

            $patient->setUser($user);
            $patient->setChecked($patientDTO->checked);
            $patient->setPhone($patientDTO->phone);
            $patient->setTypeDialysis($patientDTO->type_dialysis);
            $patient->setMedicalHistory($patientDTO->medical_history);
            $patient->setVascularAccessType($patientDTO->vascular_access_type);

            if ($patientDTO->dialysis_start_date != null) $patient->setDialysisStartDate(new \DateTimeImmutable($patientDTO->dialysis_start_date));
            else $patient->setDialysisStartDate(null);
            
            if ($patientDTO->center != null) $patient->setCenter($this->centerRepository->find($patientDTO->center));
            else $patient->setCenter(null);

            $patient->setDrugAllergies($patientDTO->drug_allergies);
            if ($patientDTO->drug_allergies == false) $patient->setDrugAllergiePrecise(null);
            else $patient->setDrugAllergiePrecise($patientDTO->drug_allergie_precise);

            // TODO: envoyer un email avec le mot de passe
            // $this->sendPasswordEmail($userDTO->email, $randomPassword);

            $this->entityManager->persist($user);
            $this->entityManager->persist($patient);

            $this->entityManager->flush();
            $this->logger->info('Création du patient : ' . $user->getId());

            return ['message' => 'La création du patient enregistrer', 'data' => $user, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la création du patient : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la création du patient', 'data' => null, 'code' => 500];
        }
    }

    public function updateInfoPatient(PatientDTO $patientDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($patientDTO));
            if ($validationResult) return $validationResult;

            $user = $this->entityManager->getRepository(user::class)->find($patientDTO->idUser);
            $patient = $user->getPatient();
            // TODO: catch l'erreur
            $user->setfirstname($patientDTO->firstname);
            $user->setLastname($patientDTO->lastname);
            $user->setEmail($patientDTO->email);

            $patient->setChecked($patientDTO->checked);
            $patient->setPhone($patientDTO->phone);
            $patient->setTypeDialysis($patientDTO->type_dialysis);
            $patient->setMedicalHistory($patientDTO->medical_history);
            $patient->setVascularAccessType($patientDTO->vascular_access_type);

            if ($patientDTO->dialysis_start_date != null) $patient->setDialysisStartDate(new \DateTimeImmutable($patientDTO->dialysis_start_date));
            else $patient->setDialysisStartDate(null);
            
            if ($patientDTO->center != null) $patient->setCenter($this->centerRepository->find($patientDTO->center));
            else $patient->setCenter(null);
            
            $patient->setDrugAllergies($patientDTO->drug_allergies);
            if($patientDTO->drug_allergies == false) $patient->setDrugAllergiePrecise(null);
            else $patient->setDrugAllergiePrecise($patientDTO->drug_allergie_precise);

            $this->entityManager->persist($user);
            $this->entityManager->persist($patient);

            $this->entityManager->flush();
            $this->logger->info('Modification du patient : ' . $user->getId());

            return ['message' => 'Les informations patient ont bien été modifié', 'data' => $user, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la sauvegarde du patient : ' . $e->getMessage());
            return ['message' => 'Probléme lors de la sauvegarde du patient', 'data' => null, 'code' => 500];
        }
    }

    public function changeStatusUser($currentUser, UserDTO $userDTO): array
    {
        try {
            // Validation des données envoyées
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($userDTO));
            if ($validationResult) return $validationResult;

            // Récupérer l'utilisateur à modifier
            $userToModify = $this->entityManager->getRepository(User::class)->find($userDTO->id);
            if (!$userToModify) {
                return ['message' => 'Utilisateur non trouvé', 'data' => null, 'code' => 404];
            }

            // Vérifier si l'utilisateur à modifier est sous sa supervision
            if (!$this->identifier->isadminOsmose($currentUser)) {
                $adminBySuperAdmin = $this->adminRepository->findAdminsBySuperAdmin($currentUser);
                // Vérifier si l'utilisateur à modifier est dans cette liste
                $userFound = false;
                foreach ($adminBySuperAdmin as $admin) {
                    if ($admin->getUser()->getId() === $userToModify->getId()) {
                        $userFound = true;
                        break;
                    }
                }

                if (!$userFound) {
                    return ['message' => 'Vous n\'avez pas les droits pour modifier cet utilisateur', 'data' => null, 'code' => 403];
                }
            }

            // Effectuer la modification du statut
            $userToModify->setValid($userDTO->valid);

            $this->entityManager->persist($userToModify);
            $this->entityManager->flush();
            $this->logger->info('Modification du statut de l\'utilisateur : ' . $userToModify->getId());

            return ['message' => 'Le statut de l\'utilisateur a bien été modifié', 'data' => $userToModify, 'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problème lors de la sauvegarde du statut de l\'utilisateur : ' . $e->getMessage());
            return ['message' => 'Problème lors de la sauvegarde du statut de l\'utilisateur', 'data' => null, 'code' => 400];
        }
    }

    private function removeCentersAdmin(Administrator $admin): bool {
        try {
            foreach ($admin->getCenters() as $center) {
                $admin->removeCenter($center);
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de la suppression des centres de l\'admin : ' . $e->getMessage());
            return false;
        }
    }

    private function addCentersAdmin(Administrator $admin, array $center_array): bool {
        try {
            foreach ($center_array as $centerId) {
                $center = $this->centerRepository->find($centerId);
                if ($center) {
                    $admin->addCenter($center);
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Probléme lors de l\'ajout des centres de l\'admin : ' . $e->getMessage());
            return false;
        }
        
    }


















    //Creation ou modification d'utilisateur par un admin pour : Patient et Admin
    public function saveUserByAdmin(FormInterface $form, User $user, bool $isAdmin): bool
    {
        try {
            if (!$this->saveSimpleUser($form, $user, $isAdmin)) return false;
            if (!$this->saveRole($form, $user, $isAdmin)) return false;
            if (!$this->savePassword($form, $user)) return false;

            if ($isAdmin) {
                $resultAdmin = $this->adminManager->saveAdmin($form, $user, $user->getAdministrator());
                if (!$resultAdmin) return false;
            } else {
                $resultPatient = $this->patientManager->savePatient($form, $user, $user->getPatient());
                if (!$resultPatient) return false;
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->logger->info('Création / Modification d\'un utilisateur (isAdmin = ' . $isAdmin . ') : ' . $user->getId());
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Création d\'un utilisateur (isAdmin = ' . $isAdmin . ') : ' . $e->getMessage());
            return false;
        }
    }

    //Creation d'utilisateur par un patient
    public function createUserByRegisterPatient(FormInterface $form, User $user): bool
    {
        try {
            if (!$this->saveSimpleUser($form, $user, false)) return false;
            if (!$this->saveRole($form, $user, false)) return false;

            $patient = new Patient();
            $patient->setChecked(false);
            $user->setPassword($this->passwordHash->hashPassword($user, $form['password']->getData()));
            $patient->setUser($user);

            $this->entityManager->persist($patient);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Création d\'un utilisateur patient : ' . $e->getMessage());
            return false;
        }
    }

    public function updatePatient(FormInterface $form, User $user): bool
    {
        try {
            $userData = $form->getData();
            $user->setFirstname($userData->getFirstname());
            $user->setLastname($userData->getLastname());
            $patient = $this->patientManager->savePatient($form, $user, $user->getPatient());
            if (!$patient) return false;

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->logger->info('Mise à jour d\'un utilisateur patient : ' . $user->getId());
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Mise à jour d\'un utilisateur patient : ' . $e->getMessage());
            return false;
        }
    }

    public function saveSimpleUser(FormInterface $form, User $user, bool $admin): bool
    {
        try {
            $firstname = $form->get('firstname')->getData();
            $lastname = $form->get('lastname')->getData();
            $email = $form->get('email')->getData();

            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setValid(true);
            $user->setDeleted(false);
            $user->setAdmin($admin);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde d\'un utilisateur : ' . $e->getMessage());
            return false;
        }
    }

    public function saveRole(FormInterface $form, User $user, bool $isAdmin): bool
    {
        try {
            if ($isAdmin && $this->identifier->isadminOsmose($this->security->getUser())) {
                return $this->saveRoleByadminOsmose($form, $user);
            } else {
                return $this->saveRoleBySimpleUser($user, $isAdmin);
            }
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde d\'un role utilisateur : ' . $e->getMessage());
            return false;
        }
    }

    public function saveRoleBySimpleUser(User $user, bool $admin): bool
    {
        try {
            if ($admin) $role = [0 => 'ROLE_ADMIN', 1 => 'ROLE_USER'];
            else $role = [0 => 'ROLE_PATIENT', 1 => 'ROLE_USER'];

            $user->setRoles($role);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde des rôles émit par un utilisateur (Patient ou Admin): ' . $e->getMessage());
            return false;
        }
    }

    public function saveRoleByadminOsmose(FormInterface $form, User $user): bool
    {
        try {
            $arrayRole = [0 => $form->get('role')->getData()->getRoleName(), 1 => 'ROLE_USER'];
            $user->setRoles($arrayRole);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde des rôles émit par un administrateur Osmose : ' . $e->getMessage());
            return false;
        }
    }

    public function savePassword(FormInterface $form, User $user): bool
    {
        try {
            $password = $form['password']->getData();

            if ($user->getPassword() != $password) {
                $user->setPassword($this->passwordHash->hashPassword($user, $password));
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Sauvegarde du mot de passe : ' . $e->getMessage());
            return false;
        }
    }

    public function changePassword(FormInterface $form, User $user): bool
    {
        try {
            $password = $form['current_password']->getData();
            $newPassword = $form['new_password']->getData();

            if ($this->passwordHash->isPasswordValid($user, $password)) {
                $user->setPassword($this->passwordHash->hashPassword($user, $newPassword));
            } else {
                return false;
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Modification du mot de passe : ' . $e->getMessage());
            return false;
        }
    }

    public function resetPassword(User $user)
    {
        try {
            $reset_password = new ResetPassword();
            $reset_password->setUser($user);
            $reset_password->setToken(uniqid());

            $this->entityManager->persist($reset_password);
            $this->entityManager->flush();
            return $reset_password;
        } catch (\Exception $e) {
            $this->logger->error('Erreur reinitilisation MDP : ' . $e->getMessage());
            return false;
        }
    }
}
