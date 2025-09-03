<?php

namespace App\Controller\centerInterface;

use App\Repository\AdministratorRepository;
use App\Repository\UserRepository;
use App\Services\Identifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private Identifier $identifier,
        private AdministratorRepository $adminRepository,
        private UserRepository $userRepository
    ) {}


    #[Route('/api/get-liste-users', name: 'app_liste_user_center')]
    public function getListeUserCenter(): Response
    {
        $user = $this->getUser();
        if ($this->identifier->isAdminDialyzone($user)) {
            $allUsers = $this->userRepository->findAdminOrPatient(true);
        } else {
            $adminBySuperAdmin = $this->adminRepository->findAdminsBySuperAdmin($user);
            foreach ($adminBySuperAdmin as $user) {
                $allUsers[] = $user->getUser();
            }
        }
        return $this->json($allUsers, 200, [], ['groups' => 'info_user']);
    }

}
