<?php

namespace App\Controller\patientInterface;

use App\Entity\User;
use App\Form\RegisterType;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use App\Services\EmailingService;
use App\Services\Jwt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserManager $userManager,
        private Jwt $jwt,
        private UserRepository $userRepository,
        private EmailingService $emailingService
    ) {}

    #[Route('/inscription', name: 'register')]
    public function registration(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userManager->createUserByRegisterPatient($form, $user);

            if ($result) {
                $this->emailingService->sendMailActivateAccount($user);
                $this->addFlash('notice', ['nature' => 'success', 'message' => 'Votre compte Dialyzone a bien été créé. <br> Nous venons de vous envoyer un lien par mail pour confirmer votre compte : <br> (pensez à regarder vos spams ou messages indésirables).<br> Merci de cliquer sur le lien pour confirmer votre compte.']);
            } else {
                $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Une erreur est survenue lors de la création de votre compte Dialyzone. Veuillez réessayer ou appeler le support technique.']);
            }

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render('register/register.html.twig', [
            'controller_name' => 'RegisterController',
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/verification/{token}', name: 'verify_user')]
    public function verifyUser(string $token): Response
    {
        if ($this->jwt->isValid($token)) {
            $payload = $this->jwt->getPayload($token);
            if ($this->jwt->isExpired($token)) {
                $user = $this->userRepository->findOneBy(['id' => $payload['user_id']]);
                $patient = $user->getPatient();
                if ($this->jwt->check($token, $_ENV['JWT_SECRET'])) {
                    if (!$patient->isChecked()) {
                        $patient->setChecked(true);
                        $this->entityManager->flush();
                        $this->addFlash('notice', ['nature' => 'success', 'message' => 'Votre compte <span class="font-weight-bold">a bien été confirmé.</span> <br> Vous pouvez donc vous connecter avec les identifiants que vous avez renseignés lors de votre inscription.']);
                    } else {
                        $this->addFlash('notice', ['nature' => 'success', 'message' => 'Votre compte <span class="font-weight-bold">a déjà été vérifié.</span> <br> Vous pouvez donc vous connecter avec les identifiants que vous avez renseignés lors de votre inscription.']);
                    }
                } else {
                    $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Une erreur est survenue lors de la validation de votre compte Dialyzone.<br> Veuillez réessayer ou appeler le support technique.']);
                }
            } else {
                return $this->render('security/confirmExpired.html.twig', ['id' => $payload['user_id']]);
            }
        }


        return $this->redirectToRoute('app_user_login');
    }

    #[Route('/resend-verify/{id}', name: 'resend_token')]
    public function resendToken(User $user): Response
    {
        if (!$user) {
            $this->addFlash('notice', ['nature' => 'info', 'danger' => 'Une erreur est survenue lors de la validation de votre compte Dialyzone.<br> Veuillez réessayer ou appeler le support technique.']);
            return $this->redirectToRoute('app_user_login');
        }
        if ($user->getPatient()->isChecked()) {
            $this->addFlash('notice', ['nature' => 'success', 'message' => 'Votre compte a déjà été vérifié. <br> Vous pouvez donc vous connecter avec les identifiants que vous avez renseignés lors de votre inscription.']);
            return $this->redirectToRoute('app_user_login');
        } else {
            $this->emailingService->sendMailActivateAccount($user);
            $this->addFlash('notice', ['nature' => 'success', 'message' => 'Un nouveau lien de vérification a été envoyé à l\'adresse e-mail communiquer lors de votre inscription à Dialyzone. <br> (pensez à regarder vos spams ou messages indésirables).']);
            return $this->redirectToRoute('app_user_login');
        }
    }
}
