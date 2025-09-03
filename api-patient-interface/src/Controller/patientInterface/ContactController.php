<?php

namespace App\Controller\patientInterface;

use App\Form\ContactType;
use App\Services\EmailingService;
use App\Services\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{
    public function __construct(
        private EmailingService $emailingService,
        private TranslatorInterface $translator
    ) {}

    #[Route('/contact', name: 'contact')]
    public function showContact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class, null,['translator' => $this->translator]);
        $form->handleRequest(($request));

        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $response = $this->emailingService->sendContact($data['email'],$data['firstname'],$data['lastname'], $data['content'],$data['subject']);
            if ($response){
                $this->addFlash('notice', ['nature'=>'success','message'=>'Merci de nous avoir contacté, notre équipe va vous répondre dans les meilleures délais.']);
            }else{
                $this->addFlash('notice', ['nature'=>'danger','message'=>'Votre message n\'a pas pu s\'envoyer correctement re-essayer ou envoyez nous un email à : dialyzon@contact.com']);
            }
            return $this->redirectToRoute('app_account');

        }

        return $this->render('pages/contact.html.twig', [
            'form' => $form->createView()
        ] );
    }
}
