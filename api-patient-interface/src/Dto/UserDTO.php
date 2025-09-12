<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    public ?int $id = null;

    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit avoir minimum {{ limit }} caractères.", maxMessage: "Le nom doit avoir maximum {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: "/^[^\d]*$/",
        message: "Le nom ne doit pas contenir de chiffres."
    )]
    public ?string $lastname = null;

    #[Assert\Length(min: 2, max: 50, minMessage: "Le prenom doit avoir minimum {{ limit }} caractères.", maxMessage: "Le prenom doit avoir maximum {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: "/^[^\d]*$/",
        message: "Le nom ne doit pas contenir de chiffres."
    )]
    public ?string $firstname = null;

    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    public ?string $email = null;

    #[Assert\All([
        new Assert\Length(min: 1),
        new Assert\Choice(choices: ["ROLE_ADMIN", "ROLE_SUPER_ADMIN", "ROLE_PATIENT", "ROLE_ADMIN_OSMOSE"], message: "Le role ne correspond pas au valeurs attendues")]
    )]
    #[Assert\Count(
        min: 1,
        minMessage: "Vous devez sélectionner un role."
    )]
    public ?array $role_array = null;

    #[Assert\All([
        new Assert\Length(min: 1),
        new Assert\Type(type: 'integer', message: "Chaque centre doit être un entier."),
    ])]
    #[Assert\Count(
        min: 1,
        minMessage: "Vous devez sélectionner au moins un centre."
    )]
    public ?array $center_array = null;

    public ?bool $valid = null;

    public ?string $current_password = null;

    public ?string $new_password = null;

    public ?array $missingFields = [];

}