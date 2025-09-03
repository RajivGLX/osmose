<?php

namespace App\Controller\adminInterface;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private UserManager $userManager
    )
    {}

    #[Route(path: '/admin/user/changevalidite/{id}', name: 'app_admin_changevalidite_user', methods: ['post'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function activate(User $user): JsonResponse
    {
        $user = $this->userRepository->changeValidite($user);

        return $this->json(['message' => 'success', 'value' => $user->isValid()]);
    }

    #[Route(path: '/admin/user/delete/{id}', name: 'app_admin_delete_user')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(User $user): JsonResponse
    {
        $user = $this->userRepository->delete($user);
        /*$this->addFlash("success","Utilisateur supprimé");
        return $this->redirectToRoute('app_admin_users');*/
        return $this->json(['message' => 'success', 'value' => $user->isDeleted()]);
    }

    #[Route(path: '/admin/modification-mot-de-passe', name: 'app_admin_changepswd')]
    #[IsGranted('ROLE_ADMIN')]
    public function changePswd(Request $request)
    {
        $user = $this->userRepository->findOneBy(['id' => $this->getUser()->getId()]);
        $form = $this->createForm(ChangePasswordFormType::class, $user, ['translator' => $this->translator]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestResponse = $this->userManager->changePassword($form,$user);
            if ($requestResponse) {
                $this->addFlash('success', $this->translator->trans('backend.user.changed_password'));
            } else {
                $this->addFlash('error', 'backend.user.new_password_must_be');
            }
            return $this->redirectToRoute('app_admin_index');
        }
        return $this->render('admin/params/changeMdpForm.html.twig', ['passwordForm' => $form]);
    }

    #[Route(path: '/admin/user/groupaction', name: 'app_admin_groupaction_user')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function groupAction(Request $request): JsonResponse
    {
        $action = $request->get('action');
        $ids = $request->get('ids');
        $users = $this->userRepository->findBy(['id' => $ids]);

        if ($action == $this->translator->trans('backend.user.deactivate')) {
            foreach ($users as $user) {
                $user->setValid(false);
                $this->entityManager->persist($user);
            }
        } elseif ($action == $this->translator->trans('backend.user.Activate')) {
            foreach ($users as $user) {
                $user->setValid(true);
                $this->entityManager->persist($user);
            }
        } elseif ($action == $this->translator->trans('backend.user.delete')) {
            foreach ($users as $user) {
                $user->setDeleted(true);
                $this->entityManager->persist($user);
            }
        } else {
            return $this->json(['message' => 'error']);
        }
        $this->entityManager->flush();

        return $this->json(['message' => 'success', 'nb' => count($users)]);
    }
}
