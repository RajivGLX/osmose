<?php

namespace App\Controller\patientInterface;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Manager\UserManager;
use App\Services\EmailingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager, 
        private UserManager $userManager, 
        private EmailingService $emailingService
    )
    {
    }

    #[Route('/mot-de-passe-oublie', name: 'app_reset_password')]
    public function index(Request $request): Response
    {
        if ($this->getUser()){
            return $this->redirectToRoute('app_account');
        }

        if ($request->get('email')){
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user){
                $reset_password = $this->userManager->resetPassword($user);
                $this->emailingService->sendMailResetPassword($user, $reset_password->getToken());

                $this->addFlash('notice',[
                    'nature' => 'success',
                    'message' => 'Un email avec un lien de réinitialisation de mot de passe vous a été envoyé sur votre adresse mail : <strong>'.$user->getEmail().'</strong><br> (Pensez a consultez vos courrier indésirable)'
                ]);

            }else{
                $this->addFlash('notice',[
                    'nature' => 'danger',
                    'message' => 'Votre adresse mail : <strong>'.$request->get('email').'</strong><br> ne correspond avec aucun compte dialyzone'
                ]);

            }
            return $this->redirectToRoute('app_reset_password');
        }
        return $this->render('security/resetPassword.html.twig');
    }




    #[Route('/modification-mot-de-passe/{token}', name: 'update_password')]
    public function updatePassword(Request $request, $token)
    {
        $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        if (!$resetPassword){
            return $this->redirectToRoute('app_reset_password');
        }

        //Verifie la validiter du token en fonction de son heure de création
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $now = new \DateTime($now->format('Y-m-d H:i:s'));
        $dateToken = $resetPassword->getCreatedAt()->modify('+ 1 hour');
        $dateToken = new \DateTime($dateToken->format('Y-m-d H:i:s'));

        if ($now > $dateToken){
            $this->addFlash('notice', ['nature'=>'danger','message' => 'Votre lien a éxpiré veuillez réitérer votre demande']);
            return $this->redirectToRoute('app_reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $result = $this->userManager->savePassword($form, $resetPassword->getUser());
            if ($result){
                $this->entityManager->flush();
                $this->addFlash('notice',[
                    'nature' => 'success',
                    'message' => 'Votre mot de passe a été modifié avec succès. Vous pouvez maintenant vous reconnecter en utilisant votre nouveau mot de passe.'
                ]);
            }else{
                $this->addFlash('notice', ['nature' => 'danger', 'message' => 'Une erreur est survenue lors de la mise à jour de votre mot de passe. Veuillez réessayer ou appeler le support technique.']);
            }
            return $this->redirectToRoute('app_user_login');

        }

        return $this->render('security/updatePassword.html.twig', [
            'form' => $form,
        ]);

    }
}
