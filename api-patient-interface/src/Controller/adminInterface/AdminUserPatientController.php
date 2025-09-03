<?php

namespace App\Controller\adminInterface;

use App\Entity\User;
use App\Form\UserInformationType;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUserPatientController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private TranslatorInterface $translator,
        private UserManager $userManager
    )
    {}


    #[Route(path: '/admin/liste-patients', name: 'app_list_patient')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function users(): Response
    {
        $allUsers = $this->userRepository->findAdminOrPatient(false);

        return $this->render('admin/user/patientList.html.twig', ['users' => $allUsers]);
    }

    #[Route(path: '/admin/user/new/patient', name: 'app_new_patient')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function newPatient(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserInformationType::class, $user, ['translator' => $this->translator]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestResult = $this->userManager->saveUserByAdmin($form, $user,false);
            if ($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.patient.add_user'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.patient.not_add_user'));
            }

            return $this->redirectToRoute('app_list_patient');
        }

        return $this->render('admin/user/patientForm.html.twig', ['patientForm' => $form]);
    }


    #[Route(path: '/admin/edit/patient/{id}', name: 'app_admin_edit_patient')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function editPatient(User $user, Request $request)
    {
        $center = $user->getPatient()->getCenter();
        $form = $this->createForm(UserInformationType::class, $user, ['translator' => $this->translator]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $requestResult = $this->userManager->saveUserByAdmin($form,$user,false);
            if ($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.user.modify_user'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.user.not_modify_user'));
            }
            return $this->redirectToRoute('app_list_patient');
        }

        return $this->render('admin/user/patientForm.html.twig', ['patientForm' => $form, 'PatientCenter' => $center]);
    }
}
