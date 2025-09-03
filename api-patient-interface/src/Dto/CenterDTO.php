<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Json;

class CenterDTO
{
    public ?int $id;

    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit avoir minimum {{ limit }} caractères.", maxMessage: "Le nom doit avoir maximum {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: "/^[^\d]*$/",
        message: "Le nom ne doit pas contenir de chiffres."
    )]
    public ?string $name;
    
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    public ?string $email;
    
    #[Assert\Regex(pattern: "/^\+?[0-9]{10,15}$/", message: "Le numéro de téléphone n'est pas valide.")]
    public $phone;

    public ?string $url = null;

    public ?string $band  = null;

    public ?string $latitude_longitude = null;

    public ?string $slug = null;

    public ?string $address = null;

    public ?string $city = null;

    #[Assert\Type('float')]
    public ?float $zipcode = null;

    public ?int $place_available = null;

    public ?string $information = null;

    #[Assert\Type('bool')]
    public ?bool $different_facturation = null;

    public ?string $address_facturation = null;

    public ?string $city_facturation = null;

    #[Assert\Type('float')]
    public ?float $zipcode_facturation = null;

    #[Assert\Type('bool')]
    public ?bool $active = null;

    #[Assert\Type('bool')]
    public ?bool $deleted = null;

    public $center_day = null;

    #[Assert\Type('float')]
    public ?float $region_id = null;

    public ?array $missingFields = [];

}
