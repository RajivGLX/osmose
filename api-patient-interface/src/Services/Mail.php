<?php

namespace App\Services;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

Class Mail
{
    private $api_key = '6e7fb0ab813d77758146210d72697f8c';
    private $api_key_secret = '52c245d2a9cc57ee6018f37dc419e702';
    private $jwt;
    private $logger;
    private $urlGenerator;

    public function __construct(Jwt $jwt, LoggerInterface $dbLogger, UrlGeneratorInterface $urlGenerator)
    {
        $this->jwt = $jwt;
        $this->logger = $dbLogger;
        $this->urlGenerator = $urlGenerator;
    }
    public function sendMailForRegister(User $user){

        $token = $this->jwt->generate(['user_id' => $user->getId()], $_ENV['JWT_SECRET']);
        $tokenLink = $this->urlGenerator->generate('verify_user', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $subject = 'Validation de votre compte Osmose.';
        $templateId = 5717634;
        $variables = [
            'token' => $tokenLink,
            'firstname' => $user->getFirstname(),
        ];

        $response = $this->sendMail($user->getEmail(), $user->getFirstname(), $subject, $variables, $templateId);
        $this->logger->info('Statut = '. $response->getStatus().' Mailjet sur la création d\'un compte.');
    }

    public function sendMailForResetPassword(User $user, string $token){
        $subject = 'Réinitialisation de votre mot de passe Osmose.';
        $templateId = 5714781;
        $tokenLink = $this->urlGenerator->generate('update_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $variables = [
            'link_password' => $tokenLink,
        ];

        $response = $this->sendMail($user->getEmail(), $user->getFirstname(), $subject, $variables, $templateId);
        $this->logger->info('Statut = '. $response->getStatus().' Mailjet sur la réinitialisation du mot de passe.');
    }

    public function sendContact(string $email, string $firstname, string $lastname, string $request_contact)
    {
//        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
//        $body = [
//            'Messages' => [
//                [
//                    'From' => [
//                        'Email' => "matisse-canal@outlook.fr",
//                        'Name' => "Osmose"
//                    ],
//                    'To' => [
//                        [
//                            'Email' => 'rajiv_91@hotmail.fr',
//                            'Name' => $lastname,
//                        ]
//                    ],
//                    'TemplateID' => 5722671,
//                    'TemplateLanguage' => true,
//                    'Subject' => 'Demandes de contact',
//                    'Variables' => [
//                        'firstname' => $firstname,
//                        'lastname' => $lastname,
//                        'email_user' => $email,
//                        'request_contact' => $request_contact,
//                    ]
//                ]
//            ]
//        ];
//        $response = $mj->post(Resources::$Email, ['body' => $body]);
//        return $response;
    }
    private function sendMail(string $toEmail, string $toName, string $subject, array $variables, int $templateId)
    {
//        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
//        $body = [
//            'Messages' => [
//                [
//                    'From' => [
//                        'Email' => "matisse-canal@outlook.fr",
//                        'Name' => "Osmose"
//                    ],
//                    'To' => [
//                        [
//                            'Email' => $toEmail,
//                            'Name' => $toName
//                        ]
//                    ],
//                    'TemplateID' => $templateId,
//                    'TemplateLanguage' => true,
//                    'Subject' => $subject,
//                    'Variables' => $variables
//                ]
//            ]
//        ];
//
//        $response = $mj->post(Resources::$Email, ['body' => $body]);
//
//        return $response;
    }


}