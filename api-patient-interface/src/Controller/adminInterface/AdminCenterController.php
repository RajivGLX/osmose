<?php

namespace App\Controller\adminInterface;

use App\Entity\Center;
use App\Manager\CenterManager;
use App\Services\Identifier;
use App\Form\CenterFormType;
use App\Repository\CenterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminCenterController extends AbstractController
{
    public function __construct(
        private CenterRepository $centerRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private CenterManager $centerManager,
        private Identifier $identifier)
    {}

    #[Route(path: '/admin/centres', name: 'app_admin_centers')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function centers(Identifier $identifier): Response
    {
        $user = $this->getUser();

        if ($identifier->isAdminDialyzone($user)){
            $centers = $this->centerRepository->findSearch();
        }else{
            $centers = $this->centerRepository->findCenterForAdmin($user->getAdministrator()->getId());
        }

        return $this->render('admin/center/centerList.html.twig', ['centers' => $centers]);
    }

    #[Route(path: '/admin/centre/creation', name: 'app_new_center')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function newCenter(Request $request)
    {
        $center = new Center();
        $center = $this->centerManager->initializeCenterSlots($center);
        $form = $this->createForm(CenterFormType::class, $center, ['translator' => $this->translator]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestResult = $this->centerManager->saveCenter($form);
            if ($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.center.modify_center'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.center.not_modify_center'));
            }

            return $this->redirectToRoute('app_admin_centers');
        }

        return $this->render('admin/center/centerForm.html.twig', ['centerForm' => $form]);
    }

    #[Route(path: '/admin/centre/modification/{id}', name: 'app_edit_center')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function editCenter(Center $center, Request $request)
    {
        $center = $this->centerManager->initializeCenterSlots($center);
        $region = null;
        $form = $this->createForm(CenterFormType::class, $center, ['translator' => $this->translator]);

        if (!$this->identifier->isAdminDialyzone($this->getUser())){
            $centerBelongAdmin = $this->identifier->isCenterBelongAdmin($this->getUser(),$center);
            if (!$centerBelongAdmin){
                $this->addFlash('error', $this->translator->trans('backend.global.unauthorized'));
                return $this->redirectToRoute('app_admin_centers');
            }
            $region = $center->getRegion();
            $form->remove('latitude_longitude');
            $form->remove('active');
            $form->remove('region');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestResult = $this->centerManager->saveCenter($form,$center,$region);
            if ($requestResult){
                $this->addFlash('success', $this->translator->trans('backend.center.modify_center'));
            }else{
                $this->addFlash('error', $this->translator->trans('backend.center.not_modify_center'));
            }

            return $this->redirectToRoute('app_admin_centers');
        }

        return $this->render('admin/center/centerForm.html.twig', ['centerForm' => $form, 'center' => $center]);
    }

    #[Route(path: '/admin/center/changevalidite/{id}', name: 'app_admin_changevalidite_center', methods: ['post'])]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function activate(Center $center): JsonResponse
    {
        $center = $this->centerManager->changeActiveCenter($center);
        $this->entityManager->flush();
        return $this->json(['message' => 'success', 'value' => $center->isActive()]);
    }

    #[Route(path: '/admin/center/delete/{id}', name: 'app_admin_delete_center')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function delete(Center $center): JsonResponse
    {
        $center = $this->centerManager->deleteCenter($center);
        $this->entityManager->flush();
        return $this->json(['message' => 'success', 'value' => $center->isDeleted()]);
    }

    #[Route(path: '/admin/center/groupaction', name: 'app_admin_groupaction_center')]
    #[IsGranted('ROLE_ADMIN_DIALYZONE')]
    public function groupAction(Request $request): JsonResponse
    {
        $action = $request->get('action');
        $ids = $request->get('ids');
        $centers = $this->centerRepository->findBy(['id' => $ids]);

        if ($action == $this->translator->trans('backend.user.deactivate')) {
            foreach ($centers as $center) {
                $center->setActive(false);
                $this->entityManager->persist($center);
            }
        } elseif ($action == $this->translator->trans('backend.user.Activate')) {
            foreach ($centers as $center) {
                $center->setActive(true);
                $this->entityManager->persist($center);
            }
        } elseif ($centers == $this->translator->trans('backend.user.delete')) {
            foreach ($centers as $center) {
                $center->setDeleted(true);
                $this->entityManager->persist($center);
            }
        } else {
            return $this->json(['message' => 'error']);
        }
        $this->entityManager->flush();

        return $this->json(['message' => 'success', 'nb' => count($centers)]);
    }
}