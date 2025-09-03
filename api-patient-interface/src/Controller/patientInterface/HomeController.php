<?php
namespace App\Controller\patientInterface;

use App\Form\ContactType;
use App\Services\EmailingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController {

    public function __construct(
        private TranslatorInterface $translator,
        private EmailingService $emailingService
    ) {}

    #[\Symfony\Component\Routing\Attribute\Route('/', name: 'home')]
    public function showHomePage()
    {
        return $this->render('pages/home.html.twig');
    }

    #[\Symfony\Component\Routing\Attribute\Route('/foire-aux-questions', name: 'faq')]
    public function showFaqPage(Request $request)
    {
        $form = $this->createForm(ContactType::class, null,['translator' => $this->translator]);
        $form->handleRequest(($request));

        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $response = $this->emailingService->sendContact(
                $data['email'],
                $data['firstname'],
                $data['lastname'], 
                $data['content'],
                $data['object']
            );
            if ($response){
                $this->addFlash('notice', ['nature'=>'success','message'=>'Merci de nous avoir contacté, notre équipe va vous répondre dans les meilleures délais.']);
            }else{
                $this->addFlash('notice', ['nature'=>'danger','message'=>'Votre message n\'a pas pu s\'envoyer correctement re-essayer ou envoyez nous un email à : dialyzon@contact.com']);
            }
            return $this->redirectToRoute('app_account');

        }

        return $this->render('pages/faq.html.twig', [
            'form' => $form->createView()
        ] );
    }


}

