<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PatientDTO
{
    public ?int $idPatient = null;

    public ?int $idUser = null;

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

    public ?int $center = null;

    #[Assert\Type('bool')]
    public ?bool $checked = null;

    public $phone = null;

    public ?string $type_dialysis = null;

    public ?string $medical_history = null;

    public ?string $vascular_access_type = null;

    public ?string $renal_failure = null;
    public ?string $renal_failure_other = null;

    public ?string $dialysis_start_date = null;

    #[Assert\Type('bool')]
    public ?bool $drug_allergies = null;
    public ?string $drug_allergie_precise = null;

    public ?array $missingFields = [];

}