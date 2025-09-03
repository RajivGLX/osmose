<?php

namespace App\Manager;

use App\Dto\ConversationDTO;
use App\Dto\MessageDTO;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\PieceJointe;
use App\Repository\ConversationRepository;
use App\Services\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessagerieManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private ConversationRepository $conversationRepo,
        private ConversationDTO $conversationDTO,
        private LoggerInterface $logger,
        private Tools $tools
    ) {}

    private function validateAndCreateMessage(MessageDTO $messageDTO): ?array
    {
        $errors = $this->validator->validate($messageDTO);
        $validationResult = $this->tools->handleValidationErrors($errors);

        if ($validationResult) {
            return $validationResult;
        }

        $conversation = $this->conversationRepo->find($messageDTO->conversationId);

        if (!$conversation) {
            $this->logger->warning('La conversation n\'existe pas.');
            return ['errors' => ['La conversation n\'existe pas.']];
        }

        $newMsg = new Message();
        $newMsg->setContenu($messageDTO->contenu);
        $newMsg->setConversation($conversation);
        $newMsg->setAuteur($messageDTO->auteur);
        $newMsg->setCreatedAt($messageDTO->createdDate);
        $conversation->setLu(true);

        $this->em->persist($newMsg);

        return ['message' => $newMsg, 'conversation' => $conversation];
    }

    private function handleAttachments(Message $newMsg, array $files): array
    {
        $filePathArray = [];

        try {
            foreach ($files as $file) {
                $validationExtension = $this->tools->verifyExtensionFile($file->getClientOriginalName());
                if (!$validationExtension) {
                    return ['errors' => ['Extension de fichier non autorisée.']];
                }

                $newPieceJointe = new PieceJointe();
                $newPieceJointe->setNom($file->getClientOriginalName());
                $newPieceJointe->setExtension($file->getClientOriginalExtension());
                $newPieceJointe->setFichier('temp');
                $newPieceJointe->setMessage($newMsg);
                $newMsg->addPieceJointe($newPieceJointe);
                $this->em->persist($newPieceJointe);
            }

            $this->em->flush();

            foreach ($newMsg->getPieceJointes() as $pieceJointe) {
                $filePath = $this->tools->createFilePathForMessagerie(
                    $newMsg->getConversation()->getId(),
                    $newMsg->getId(),
                    $pieceJointe->getId(),
                    $pieceJointe->getExtension()
                );
                $pieceJointe->setFichier($filePath);
                $this->em->flush();
                $filePathArray[$pieceJointe->getNom()] = $filePath;
            }

            return ['paths' => $filePathArray];
        } catch (Exception $e) {
            $this->logger->error('Erreur lors de la gestion des pièces jointes : ' . $e->getMessage());
            throw $e; // Relancer l'exception pour qu'elle soit capturée par le bloc try-catch de la méthode publique
        }
    }

    private function sendFileToAws(MessageDTO $messageDTO, array $paths)
    {
        // try {
        //     foreach ($messageDTO->files as $file) {
        //         $resultAws = $this->awsService->sendFileToAws($file, $paths);
        //         if ($resultAws == false) {
        //         $this->logger->error('Erreur transport AWS');
        //         return ['errors' => 'Erreur transport AWS'];
        //         }
        //     }
        //     return ['success' => 1];
        // } catch (\Exception $e) {
        //     $this->logger->error('Erreur lors de l\'envoi du fichier sur AWS : ' . $e->getMessage());
        //     return ['errors' => ['Erreur lors de l\'envoi du fichier sur AWS : ' . $e->getMessage()]];
        // }
    }

    /**
     * Création d'un message avec ou sans fichier
     */
    public function createMessage(MessageDTO $messageDTO): array
    {
        try {
            $result = $this->validateAndCreateMessage($messageDTO);

            if (isset($result['errors'])) {
                return $result;
            }

            $newMsg = $result['message'];

            if (!empty($messageDTO->files)) {
                $fileResult = $this->handleAttachments($newMsg, $messageDTO->files);

                if (isset($fileResult['errors'])) {
                    return $fileResult;
                }

                $awsResult = $this->sendFileToAws($messageDTO, $fileResult['paths']);
                return $awsResult;
            }

            $this->em->flush();

            return ['conversation' => $result['conversation']];
        } catch (Exception $e) {
            $this->logger->error('Erreur lors de la création du message : ' . $e->getMessage());
            return ['errors' => ['Erreur lors de la création du message.']];
        }
    }

    /**
     * Marque une conversation comme lue
     */
    public function markConversationRead(Conversation $conversation)
    {
        if (!$conversation) {
            $this->logger->warning('La conversation n\'existe pas.');
            return ['errors' => ['La conversation n\'existe pas.']];
        } else {
            $conversation->setLu(true);
            $this->em->persist($conversation);
            $this->em->flush();
            return ['success' => true];
        }
    }

    /**
     * Reception d'un message venant de commercial
     */
    public function receiveMessage(MessageDTO $messageDTO)
    {
        // $errors = $this->validator->validate($messageDTO);
        // $validationResult = $this->tools->handleValidationErrors($errors);

        // if ($validationResult) {
        //     return $validationResult;
        // }

        // try {
        //     $conversation = $this->conversationRepo->find($messageDTO->conversationId);

        //     if (!$conversation) {
        //         $this->logger->warning('La conversation n\'existe pas.');
        //         return ['errors' => ['La conversation n\'existe pas.']];
        //     } elseif ($messageDTO->commercialId === null) {
        //         $this->logger->warning('Id commercial manquant.');
        //         return ['errors' => ['Id commercial manquant.']];
        //     } else if ($conversation->getCommercial() != 0 && $conversation->getCommercial() != $messageDTO->commercialId) {
        //         $this->logger->warning('Vous n\'avez pas le droit de poster dans cette conversation.');
        //         return ['errors' => ['Vous n\'avez pas le droit de poster dans cette conversation.']];
        //     } else {
        //         //Si aucun commercial n'est défini, on le définit
        //         if ($conversation->getCommercial() == 0) {
        //             $conversation->setCommercial($messageDTO->commercialId);
        //         }

        //         $newMsg = new Message();
        //         $newMsg->setContenu($messageDTO->contenu);
        //         $newMsg->setConversation($conversation);
        //         $newMsg->setAuteur($messageDTO->auteur);
        //         $newMsg->setCreatedAt($messageDTO->createdDate);
        //         $conversation->setLu(false);

        //         $this->em->persist($newMsg);
        //         $this->em->flush();

        //         return ['conversation' => $conversation];
        //     }
        // } catch (Exception $e) {
        //     $this->logger->error($e->getMessage());
        //     return ['error' => 'receiveMessage : ' . $e->getMessage()];
        // }
    }
    /**
     * Création d'une conversation par un utilisateur
     */
    public function createConversation(ConversationDTO $conversationDTO): array
    {
        $errors = $this->validator->validate($conversationDTO);
        $validationResult = $this->tools->handleValidationErrors($errors);

        if ($validationResult) {
            return $validationResult;
        }

        try {
            $conversation = new Conversation();
            $conversation->setTitre($conversationDTO->titre);
            $conversation->setService($conversationDTO->service);
            $conversation->setUser($conversationDTO->user);
            $conversation->setCreatedAt($conversationDTO->createdDate);

            $this->em->persist($conversation);
            $this->em->flush();

            return ['conversation' => $conversation];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return ['error' => 'createConversation : ' . $e->getMessage()];
        }
    }
}
