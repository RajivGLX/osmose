<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_admin_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private JWTTokenManagerInterface $jwtManager,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        $data = json_decode($request->getContent(), true);
        $email = $data['username'] ?? null;
        $user = $this->userRepository->findOneBy(["email" => $email]);
        $user->setNbConnectionAttempt(0);
        $this->entityManager->flush();

        return new Response(
            json_encode([
                'message' => 'Authentication successful!',
                'token' => $jwt
            ]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['username'] ?? null;

        $user = $this->userRepository->findOneBy(["email" => $email]);
        if (!$user) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
        $nb_tentatives_actuelles = $user->getNbConnectionAttempt();
        $user->setNbConnectionAttempt($nb_tentatives_actuelles + 1);
        $this->entityManager->flush();

        if($nb_tentatives_actuelles + 1 >= 5){
            $user->setBlockedAccount(true);
            $this->entityManager->flush();
            return new JsonResponse([
                "message" => "Votre compte est désormais bloqué, veuillez contacter votre administrateur",
                "nb_connection_attempt" => $user->getNbConnectionAttempt()
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse([
            "message" => $exception->getMessage(),
            "nb_connection_attempt" => $user->getNbConnectionAttempt()
        ],
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
