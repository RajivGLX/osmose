<?php

namespace App\Manager;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Dto\TaskDTO;
use App\Entity\Task;
use App\Services\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TaskManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Tools $tools,
        private ValidatorInterface $validator,
    ) {}

    public function createTask(TaskDTO $taskDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($taskDTO));
            if ($validationResult) return $validationResult;

            $task = new Task();
            $task->setDescription($taskDTO->description);
            $task->setChecked($taskDTO->checked);
            $task->setDate(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));

            $this->entityManager->persist($task);
            $this->entityManager->flush();
            $this->logger->info('Ajout de la tâche id : ' . $task->getId());


            return ['message' => 'La tâche a bien été créé', 'data' => $task,  'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problème lors de la création de la tâche : ' . $e->getMessage());
            return ['message' => 'Problème lors de la création de la tâche', 'data' => null, 'code' => 500];
        }
    }

    public function updateStatusTask(TaskDTO $taskDTO): array
    {
        try {
            $validationResult = $this->tools->handleValidationErrors($this->validator->validate($taskDTO));
            if ($validationResult) return $validationResult;

            $task = $this->entityManager->getRepository(Task::class)->find($taskDTO->id);
            if (!$task) {
                return ['message' => 'La tâche n\'existe pas', 'data' => null, 'code' => 404];
            }

            $task->setChecked($taskDTO->checked);
            $this->entityManager->flush();
            $this->logger->info('Modification du status de la tâche id : ' . $task->getId());

            return ['message' => 'Le status de la tâche a bien été modifié', 'data' => $task,  'code' => 200];
        } catch (\Exception $e) {
            $this->logger->error('Problème lors de la modification du status de la tâche : ' . $e->getMessage());
            return ['message' => 'Problème lors de la modification du status de la tâche', 'data' => null, 'code' => 500];
        }
    }

}
