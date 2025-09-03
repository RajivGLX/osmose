<?php

namespace App\Entity;

use App\Repository\PathologiesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PathologiesRepository::class)]
class Pathologies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $heart_disease = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $diabetes = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $musculoskeletal_problems = null;

    #[ORM\OneToOne(inversedBy: 'pathologies', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $bool_heart_disease = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $bool_diabetes = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $bool_musculoskeletal_problems = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeartDisease(): ?string
    {
        return $this->heart_disease;
    }

    public function setHeartDisease(?string $heart_disease): static
    {
        $this->heart_disease = $heart_disease;

        return $this;
    }

    public function getDiabetes(): ?string
    {
        return $this->diabetes;
    }

    public function setDiabetes(?string $diabetes): static
    {
        $this->diabetes = $diabetes;

        return $this;
    }

    public function getMusculoskeletalProblems(): ?string
    {
        return $this->musculoskeletal_problems;
    }

    public function setMusculoskeletalProblems(?string $musculoskeletal_problems): static
    {
        $this->musculoskeletal_problems = $musculoskeletal_problems;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function isBoolHeartDisease(): ?bool
    {
        return $this->bool_heart_disease;
    }

    public function setBoolHeartDisease(?bool $bool_heart_disease): static
    {
        $this->bool_heart_disease = $bool_heart_disease;

        return $this;
    }

    public function isBoolDiabetes(): ?bool
    {
        return $this->bool_diabetes;
    }

    public function setBoolDiabetes(?bool $bool_diabetes): static
    {
        $this->bool_diabetes = $bool_diabetes;

        return $this;
    }

    public function isBoolMusculoskeletalProblems(): ?bool
    {
        return $this->bool_musculoskeletal_problems;
    }

    public function setBoolMusculoskeletalProblems(?bool $bool_musculoskeletal_problems): static
    {
        $this->bool_musculoskeletal_problems = $bool_musculoskeletal_problems;

        return $this;
    }
}
