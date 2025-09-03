<?php

namespace App\Controller\patientInterface;

use App\Form\SearchCenterType;
use App\Model\SearchData;
use App\Repository\CenterRepository;
use App\Repository\RegionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CenterController extends AbstractController
{
    public function __construct(
        private CenterRepository $centerRepository,
        private RegionRepository $regionRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/centre/recherche', name: 'search_center')]
    public function listCenter(Request $request)
    {
        $searchData = new SearchData();
        $numberResult = null;
        $query = null;
        $resultFilter = [];
        $allRegions = $this->regionRepository->findAll();
        $form = $this->createForm(SearchCenterType::class, $searchData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $pagination = $this->paginator->paginate($this->centerRepository->findSearch($searchData), $request->query->get('page', 1), 8);
            $numberResult = $pagination->getTotalItemCount();
            $resultSearch = $searchData->query;
            $resultFilter = $searchData->region;
            if ($numberResult === 0){
                if(!empty($resultFilter)){
                    $message = 'Aucun résultat trouvé avec la recherche: <strong>'.$resultSearch.'</strong> et le filtre utilisé';
                }else{
                    $message = 'Aucun résultat trouvé avec la recherche: <strong>'.$resultSearch.'</strong>';
                }
                $this->addFlash('notice',['nature' => 'danger', 'message' => $message]);
            }

        }else{
            $pagination = $this->paginator->paginate($this->centerRepository->findSearch($searchData), $request->query->get('page', 1), 8);
        }


        return $this->render('patientInterface/pages/listCenter.html.twig',[
            'form'=> $form->createView(),
            'pagination'=> $pagination,
            'allRegions' => $allRegions,
            'result'=> $numberResult,
            'query' => $query,
            'resultFilter' => $resultFilter,
        ]);
    }

}
