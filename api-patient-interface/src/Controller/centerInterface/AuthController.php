<?php
// src/Controller/AuthController.php
namespace App\Controller\centerInterface;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\LoginLimiter;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private $loginLimiter;

    public function __construct(LoginLimiter $loginLimiter)
    {
        $this->loginLimiter = $loginLimiter;
    }

    #[Route('/api/login_check_status', name: 'login_check_status', methods: ['GET'])]
    public function checkLoginStatus(): JsonResponse
    {
        $remainingAttempts = $this->loginLimiter->getRemainingAttempts();
        $blocked = $this->loginLimiter->limitReached();

        return $this->json([
            'blocked' => $blocked,
            'remainingAttempts' => $remainingAttempts
        ]);
    }

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User registered successfully']);
    }

    #[Route('/api/token/refresh', methods: ['POST'])]
    public function refreshToken(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        try {
            // Récupérer le token depuis l'en-tête Authorization
            $authHeader = $request->headers->get('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json(['message' => 'Token manquant'], Response::HTTP_UNAUTHORIZED);
            }

            $token = substr($authHeader, 7); // Supprimer "Bearer"

            // Décoder le token pour récupérer l'utilisateur
            $decodedToken = $jwtManager->parse($token);
            $userEmail = $decodedToken['username'] ?? null;

            if (!$userEmail) {
                return $this->json(['message' => 'Token invalide'], Response::HTTP_UNAUTHORIZED);
            }

            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
            }


            // Générer un nouveau token
            $newToken = $jwtManager->create($user);

            return $this->json([
                'token' => $newToken,
                'message' => 'Token rafraîchi avec succès'
            ], Response::HTTP_OK);

        } catch (JWTDecodeFailureException $e) {
            return $this->json(['message' => 'Token invalide ou expiré'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors du rafraîchissement du token'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}