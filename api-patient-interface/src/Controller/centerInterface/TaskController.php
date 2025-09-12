<?php

namespace App\Controller\centerInterface;


use App\Dto\TaskDTO;
use App\Manager\TaskManager;
use App\Repository\TaskRepository;
use App\Services\Tools;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private TaskDTO $taskDTO,
        private TaskManager $taskManager,
        private TaskRepository $taskRepository,
        private Tools $tools,
        private LoggerInterface $logger,
    )
    {
    }

    #[Route('/api/get-all-task', name: 'api_get_all_task', methods: ['GET'])]
    public function getAllTask(): Response
    {
            $allTask = $this->taskRepository->findAll();
            return $this->json(['data' => $allTask, 'message' => 'Chargement des tâches réussis'], 200, [], ['groups' => 'info_task']);
    }


    #[Route('/api/create-task', name: 'api_create_task', methods: ['POST'])]
    public function createTask(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['description', 'checked'];
        $taskDTO = $this->tools->requiredFields($requiredFields, $data, $this->taskDTO);

        if (!empty($taskDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la création de la tâche : ' . json_encode($taskDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        }
        $result = $this->taskManager->createTask($taskDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_task']);
    }

    #[Route('/api/task-update-status', name: 'api_task_update_status', methods: ['POST'])]
    public function updateStatusTask(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $requiredFields = ['id','checked'];
        $taskDTO = $this->tools->requiredFields($requiredFields, $data, $this->taskDTO);

        if (!empty($taskDTO->missingFields)) {
            $this->logger->error('Champs vide lors de la modification du status du task : ' . json_encode($taskDTO->missingFields));
            return $this->json(['message' => 'Champs vide lors de l\'envoie du formulaire'], 400);
        }
        $result = $this->taskManager->updateStatusTask($taskDTO);

        return $this->json(['message' => $result['message'], 'data' => $result['data']], $result['code'], [], ['groups' => 'info_task']);
    }



}
