<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\ConversationRepository;
use Psr\Log\LoggerInterface;

class Tools
{
    public function __construct(
        private LoggerInterface $logger,
    )
    {}

    public function handleValidationErrors($errors): array
    {
        if (count($errors) > 0) {
            $errorMessages = ['errors' => []];
            foreach ($errors as $error) {
                $errorMessages['errors'][] = $error->getMessage();
            }
            $this->logger->error('Validation error : ' . json_encode($errorMessages));
            return ['message' => 'Données envoyées corrompue', 'data' => null, 'code' => 400];
        }
        return [];
    }

    public function requiredFields(array $allFields, array $data, $DTO){
        try {
            foreach ($allFields as $field) {
                if (isset($data[$field])) {
                    $DTO->$field = $data[$field];
                } else {
                    array_push($DTO->missingFields, $field);
                }
            }
            return $DTO;
        } catch (\Exception $e) {
            $this->logger->error('Error required fields : ' . $e->getMessage());
            return ['message' => 'Erreur champs manquant', 'data' => null, 'code' => 500];
        }
    }

    public function otherFields(array $allFields, array $data, $DTO)
    {
        try {
            foreach ($allFields as $field) {
                if (isset($data[$field])) {
                    $DTO->$field = $data[$field];
                }
            }
            return $DTO;
        } catch (\Exception $e) {
            $this->logger->error('Error other fields : ' . $e->getMessage());
            return ['message' => 'Erreur d\'envoie de formulaire', 'data' => null, 'code' => 500];
        }
    }

    public function verifyExtensionFile(string $filename): bool
    {
        $allowed =  array('png' ,'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', '7z');
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed) ) {
            return false;
        }
        return true;
    }

}
