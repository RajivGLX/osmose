<?php

namespace App\Controller\centerInterface;

use App\Dto\CenterDTO;
use App\Manager\CenterManager;
use App\Repository\AvailabilityRepository;
use App\Repository\CenterRepository;
use App\Repository\RegionRepository;
use App\Services\Identifier;
use App\Services\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CenterController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private Identifier $identifier,
        private Tools $tools,
        private AvailabilityRepository $availabilityRepository,
        private CenterRepository $centerRepository,
        private RegionRepository $regionRepository,
        private CenterManager $centerManager,
        private CenterDTO $centerDTO,
    ) {}

    
    #[Route('/api/availability-center', name: 'app_availability_center', methods: ['POST'])]
    public function availabilityCenter(Request $request): Response
    {
        $idCenter = json_decode($request->getContent());

        // return $this->json($idCenter, 200, [], ['groups' => ['availability']]);

        $availability = $this->availabilityRepository->findAvailabilityOnOneYearByCenter($idCenter);

        return $this->json($availability, 200, [], ['groups' => ['availability']]);
    }


    #[Route('/api/get-center', name: 'api_get_center', methods: ['GET'])]
    public function getCenter(Request $request): Response
    {
        // TODO: Vérifier si l'utilisateur a les droits pour accéder à cette page
        $idCenter = $request->query->get('id');
        $center = $this->centerRepository->find($idCenter);
        
        if (!$center) {
            return $this->json(['message' => 'Centre non trouvé'], 404);
        }

        return $this->json($center, 200, [], ['groups' => 'info_center']);
    }


    #[Route('/api/get-all-centers', name: 'api_get_all_centers', methods: ['GET'])]
    public function getAllCenters(): Response
    {
        // TODO: Vérifier si l'utilisateur a les droits pour accéder à cette page
        $center = $this->centerRepository->findAll();

        return $this->json($center, 200, [], ['groups' => 'info_center']);
    }


    #[Route('/api/get-list-center', name: 'api_list_center', methods: ['GET'])]
    public function getListCenters(): Response
    {
        if ($this->identifier->isAdminDialyzone($this->getUser())) {
            $listCenters = $this->centerRepository->findAll();
            return $this->json(['data' => $listCenters, 'message' => 'Chargement des centres reussis'], 200, [], ['groups' => 'info_center']);
        } else {
            return $this->json(['message' => 'Vous n\'avez pas les droits pour accéder à cette page'], 403);
        }
    }


    #[Route('/api/get-all-regions', name: 'api_all_regions', methods: ['GET'])]
    public function getAllRegions(): Response
    {
        $allRegions = $this->regionRepository->findAll();
        return $this->json($allRegions, 200, [], ['groups' => 'info_region']);
    }


    #[Route('/api/create-center', name: 'api_create_center', methods: ['POST'])]
    public function createCenter(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['name', 'email', 'phone', 'url', 'place_available', 'region_id', 'active','address', 'city', 'zipcode', 'different_facturation', 'center_day'];
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        $centerDTO->band = $data['band'] ?? null;
        $centerDTO->information = $data['information'] ?? null;
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        if ($centerDTO->different_facturation) {
            $requiredFields = ['address_facturation', 'city_facturation', 'zipcode_facturation'];
            $centerDTO = $this->tools->requiredFields($requiredFields, $data, $centerDTO);
        }
        if (!empty($centerDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la création du centre : ' . json_encode($centerDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        } 
        $result = $this->centerManager->createCenter($centerDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);
    }

    #[Route('/api/update-center-info', name: 'api_update_center_info', methods: ['POST'])]
    public function updateCenterInfo(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'name', 'email', 'phone', 'url', 'place_available', 'region_id'];
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        // Traiter le champ 'band' séparément car il peut être null
        $centerDTO->band = $data['band'] ?? null;
        $centerDTO->information = $data['information'] ?? null;
        if (!empty($centerDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modification des infos du centre : ' . json_encode($centerDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        }
        $result = $this->centerManager->updateInfoCenter($centerDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);
    }


    #[Route('/api/update-center-address', name: 'api_update_center_address', methods: ['POST'])]
    public function updateCenterAddress(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'address', 'city', 'zipcode', 'different_facturation'];
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        if ($centerDTO->different_facturation) {
            $requiredFields = ['address_facturation', 'city_facturation', 'zipcode_facturation'];
            $centerDTO = $this->tools->requiredFields($requiredFields, $data, $centerDTO);
        }
        if (!empty($centerDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation de l\'adresse du centre : '. json_encode($centerDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        } 
        $result = $this->centerManager->updateAddressCenter($centerDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);
    }


    #[Route('/api/update-center-day', name: 'api_update_center_day', methods: ['POST'])]
    public function updateCenterDay(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'center_day'];
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        if (!empty($centerDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation des horaires du centre : ' . json_encode($centerDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire', 'data' => null], 400);
        }
        $result = $this->centerManager->updateCenterDay($centerDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);
    }


    #[Route('/api/center-change-status', name: 'api_update_center_status', methods: ['POST'])]
    public function updateCenterStatus(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id', 'active'];
        $centerDTO = $this->tools->requiredFields($requiredFields, $data, $this->centerDTO);
        if (!empty($centerDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modifcation du status du centre : ' . json_encode($centerDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        }
        $result = $this->centerManager->changeStatusCenter($this->getUser(), $centerDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_center']);
    }
}
