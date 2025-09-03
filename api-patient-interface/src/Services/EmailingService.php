<?php

namespace App\Services;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class EmailingService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private LoggerInterface $logger,
        private Jwt $jwt,
        private UrlGeneratorInterface $urlGenerator
    ) {}


    public function sendMailActivateAccount(User $user):bool
    {
        $token = $this->jwt->generate(['user_id' => $user->getId()], $_ENV['JWT_SECRET']);
        $tokenLink = $this->urlGenerator->generate('verify_user', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'])
            ->to($user->getEmail())
            ->subject('Activation de compte Dialyzone')
            ->htmlTemplate('/email/emailActivateAccount.txt.twig')
            ->htmlTemplate('/email/emailActivateAccount.html.twig')
            ->context([
                'sujet' => 'Votre compte a bien été créé.',
                'user' => $user,
                'lien' => $tokenLink,
                'entreprise' => [
                    "nom" => 'Dialyzone',
                    "adresse" => '10 avenue de Provence',
                    "cp" => '91940',
                    "ville" => "Les Ulis",
                    "pays" => "France"
                ],
            ]);
        $this->mailer->send($email);
        $this->logger->info('Email activate account for ' . $user->getEmail() . ' sent successfully');
        return true;
    }

    public function sendMailResetPassword(User $user, $token): bool
    {
        $tokenLink = $this->urlGenerator->generate('update_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'])
            ->to($user->getEmail())
            ->subject('Réinitialisation de mot de passe Dialyzone')
            ->textTemplate('/email/resetPassword.txt.twig')
            ->htmlTemplate('/email/resetPassword.html.twig')
            ->context([
                'title' => 'Réinitialisation du mot de passe',
                'sujet' => 'Votre demande de réinitialisation de mot de passe a bien été prise en compte.',
                'user' => $user,
                'lien' => $tokenLink,
            ]);

        $this->mailer->send($email);
        return true;
    }

    public function sendContact(string $emailUser, string $firstname, string $lastname, string $request_contact, string $object):bool
    {
        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'])
            ->to('contact@dialyzone.com')
            ->subject('Contact Dialyzone : '.$object)
            ->textTemplate('/email/EmailContact.txt.twig')
            ->htmlTemplate('/email/EmailContact.html.twig')
            ->context([
                'title' => 'Réinitialisation du mot de passe',
                'sujet' => $object,
                'email' => $emailUser,
                'request' => $request_contact,
                'firstname' => $firstname,
                'lastname' => $lastname,
            ]);

        $this->mailer->send($email);
        $this->logger->info('Email contact for ' . $emailUser . ' sent successfully');
        return true;
    }
}
