<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDTO
{
    #[Assert\NotBlank(message: "Le conversationId de la conversation ne doit pas être vide.")]
    #[Assert\Type(
        type: 'integer',
        message: "Le conversationId de la conversation doit être un entier."
    )]
    public $conversationId;

    #[Assert\NotBlank(message: "Le contenu du message ne doit pas être vide.")]
    public $contenu;

    #[Assert\NotBlank(message: "La date de création du message ne doit pas être vide.")]
    public $createdDate;

    public $auteur;

    public $commercialId;

    public $files;
}
