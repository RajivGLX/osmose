<?php

namespace App\Controller;
use App\Repository\UserRepository;

use App\Services\EmailingService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
	public function __construct(private UserPasswordHasherInterface $passwordHasher,private EmailingService $emailingService) {}

	/**
	 * Envoi un mail de réinitialisation de mdp et retourne un message
	 *
	 * @param Request $request
	 * @param UserRepository $userRepository
	 * @param EntityManagerInterface $manager
	 * @param JWTEncoderInterface $tokenInterface
	 * @return JsonResponse
	 */
	#[Route('/reset-password-mail', methods: ['POST'])]
	public function forgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $manager, JWTEncoderInterface $tokenInterface): JsonResponse
	{
		$data = json_decode($request->getContent(), true);
		$email = $data;

		if (isset($email)) {
			$user = $userRepository->findOneBy(['email' => $email]);

			// if ($user) {
			// 	$user->setTokenResetPassword($tokenInterface->encode([
			// 		'email' => $email,
			// 		'exp' => (new \DateTime('+ 10 minute'))->getTimestamp()
			// 	]));

			// 	try {
			// 		$manager->persist($user);
			// 		$manager->flush();
            //         $this->emailingService->sendMailResetPassword($user);
			// 		return $this->json([
			// 			'message' => 'Un lien vous a été envoyé'
			// 		], Response::HTTP_OK);
			// 	} catch (\Exception $e) {
			// 		return $this->json([
			// 			'message' => 'Une erreur s\'est produite'
			// 		], Response::HTTP_FORBIDDEN);
			// 	}
			// }

			return $this->json([
				'message' => 'Votre compte est introuvable'
			], Response::HTTP_FORBIDDEN);
		}

		return $this->json([
			'message' => 'Votre adresse email ne semble pas être connue'
		], Response::HTTP_FORBIDDEN);
	}

	/**
	 * Enregistre le nouveau mdp de l'utilisateur et retourne un message
	 *
	 * @param Request $request
	 * @param UserRepository $userRepository
	 * @param EntityManagerInterface $manager
	 * @param JWTTokenManagerInterface $tokenManager

	 * @return JsonResponse
	 */
	#[Route('/valid-new-password', methods: ['POST'])]
	public function newPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $manager, JWTTokenManagerInterface $tokenManager): JsonResponse
	{
		$data = json_decode($request->getContent(), true);
		$user = $userRepository->findOneBy(['token_reset_password' => $data['token']]);
		return $this->json([
			'message' => 'Votre compte est introuvable'
		], Response::HTTP_FORBIDDEN);

		// try {
		// 	if ($user) {
		// 		$tokenExp = $tokenManager->parse($data['token'])['exp'];

		// 		if ($tokenExp > time()) {
		// 			$user->setPassword($this->passwordHasher->hashPassword($user, $data['newPassword']));
		// 			$user->setTokenResetPassword(null);

		// 			$manager->persist($user);
		// 			$manager->flush();

		// 			return $this->json([
		// 				'message' => 'Votre mot de passe a été modifié avec succès'
		// 			], Response::HTTP_OK);
		// 		}
		// 	}

		// 	return $this->json([
		// 		'message' => 'Votre compte est introuvable'
		// 	], Response::HTTP_FORBIDDEN);
		// } catch (JWTDecodeFailureException $e) {
		// 	$user->setTokenResetPassword(null);

		// 	$manager->persist($user);
		// 	$manager->flush();

		// 	return $this->json([
		// 		'message' => 'Ce lien est expiré\nVeuillez refaire une demande de réinitialisation'
		// 	], Response::HTTP_FORBIDDEN);
		// }
	}
}
