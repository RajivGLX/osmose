<?php

namespace App\Controller\adminInterface;

use App\Entity\User;
use App\Form\AdminFormType;
use App\Manager\UserManager;
use App\Repository\AdministratorRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Services\Identifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUserAdminController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private AdministratorRepository $adminRepository,
        private TranslatorInterface $translator,
        private RoleRepository $roleRepository,
        private Identifier $identifier,
        private UserManager $userManager
    )
    {}

    #[Route(path: '/admin/liste-admin', name: 'app_list_admin')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function listAdmin(): Response
    {
        $user = $this->getUser();
        if ($this->identifier->isAdminDialyzone($user)){
            $allUsers = $this->userRepository->findAdminOrPatient(true);
        }else{
            $adminBySuperAdmin = $this->adminRepository->findAdminsBySuperAdmin($user);
            foreach ($adminBySuperAdmin as $user){
                $allUsers[] = $user->getUser();
            }
        }

        return $this->render('admin/user/adminList.html.twig', ['users' => $allUsers]);
    }

    #[Route(path: '/admin/creation/admin', name: 'app_new_admin')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function newAdmin(Request $request)
    {
        $user = new User();
        $form = $this->createForm(AdminFormType::class, $user, ['translator' => $this->translator]);

        // Si se n'est pas un Admin Dializone, on enlève le champ role
        if (!$this->identifier->isAdminDialyzone($this->getUser())){
            $form->remove('role');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestResult = $this->userManager->saveUserByAdmin($form,$user,true);
            if ($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.admin.add_user'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.admin.not_add_user'));
            }

            return $this->redirectToRoute('app_list_admin');
        }

        return $this->render('admin/user/adminForm.html.twig', ['userForm' => $form]);
    }


    #[Route(path: '/admin/modification/admin/{id}', name: 'app_edit_admin')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function editAdmin(User $user, Request $request)
    {
        $centerAssignAdminEdit = $this->adminRepository->getCentersAdmin($user,true);

        $form = $this->createForm(AdminFormType::class, $user, ['translator' => $this->translator]);
        $form->get('password')->setData($user->getPassword());

        // Si l'utilisateur connecté est égale à l'utilisateur en cour de modif, on enlève le centre
        if ($user ==  $this->getUser())$form->remove('center');

        $isAdminDialyzone = $this->identifier->isAdminDialyzone($this->getUser());
        $form->get('role');
        $form->setData($isAdminDialyzone ? $this->roleRepository->findOneBy(['roleName' => $user->getRoles()[0]]) : null);
        $form->remove($isAdminDialyzone ? 'service' : 'role');
        $centerOfSuperAdmin = $isAdminDialyzone ? null : $this->adminRepository->getCentersAdmin($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->userManager->saveUserByAdmin($form,$user,true);
            if ($result) {
                $this->addFlash('success', $this->translator->trans('backend.user.modify_user'));
            } else{
                $this->addFlash('error', $this->translator->trans('backend.user.not_modify_user'));
            }
            return $this->redirectToRoute('app_list_admin');
        }

        return $this->render('admin/user/adminForm.html.twig', [
            'userForm' => $form,
            'allCentersAdminEdit' => $centerAssignAdminEdit,
            'centerOfSuperAdmin' => $centerOfSuperAdmin,
            'user' => $user,
        ]);
    }

}
