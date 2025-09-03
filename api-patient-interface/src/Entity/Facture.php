<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?string $number = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user'])]
    private ?string $mean_of_payment = null;

    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?float $amount_excluding_taxes = null;

    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?float $amount_all_charges = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?bool $state = null;

    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?center $center = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(float $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getMeanOfPayment(): ?string
    {
        return $this->mean_of_payment;
    }

    public function setMeanOfPayment(string $mean_of_payment): static
    {
        $this->mean_of_payment = $mean_of_payment;

        return $this;
    }

    public function getAmountExcludingTaxes(): ?float
    {
        return $this->amount_excluding_taxes;
    }

    public function setAmountExcludingTaxes(float $amount_excluding_taxes): static
    {
        $this->amount_excluding_taxes = $amount_excluding_taxes;

        return $this;
    }

    public function getAmountAllCharges(): ?float
    {
        return $this->amount_all_charges;
    }

    public function setAmountAllCharges(float $amount_all_charges): static
    {
        $this->amount_all_charges = $amount_all_charges;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCenter(): ?center
    {
        return $this->center;
    }

    public function setCenter(?center $center): static
    {
        $this->center = $center;

        return $this;
    }
}
