<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user','info_booking'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $medical_history = null;

    #[ORM\ManyToOne(inversedBy: 'patients')]
    #[Groups(['info_user', 'info_booking'])]
    private ?Center $center = null;

    #[ORM\OneToOne(inversedBy: 'patient', cascade: ['persist', 'remove'])]
    #[Groups(['info_booking'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Booking::class)]
    private Collection $booking;

    #[ORM\Column]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $checked = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $renal_failure = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $type_dialysis = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $drug_allergies = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $drug_allergie_precise = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?\DateTimeImmutable $dialysis_start_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $vascular_access_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $renal_failure_other = null;

    public function __construct()
    {
        $this->booking = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMedicalHistory(): ?string
    {
        return $this->medical_history;
    }

    public function setMedicalHistory(?string $medical_history): static
    {
        $this->medical_history = $medical_history;

        return $this;
    }

    public function getCenter(): ?Center
    {
        return $this->center;
    }

    public function setCenter(?Center $center): static
    {
        $this->center = $center;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBooking(): Collection
    {
        return $this->booking;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->booking->contains($booking)) {
            $this->booking->add($booking);
            $booking->setPatient($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->booking->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getPatient() === $this) {
                $booking->setPatient(null);
            }
        }

        return $this;
    }

    public function isChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): static
    {
        $this->checked = $checked;

        return $this;
    }

    public function getRenalFailure(): ?string
    {
        return $this->renal_failure;
    }

    public function setRenalFailure(?string $renal_failure): static
    {
        $this->renal_failure = $renal_failure;

        return $this;
    }

    public function getTypeDialysis(): ?string
    {
        return $this->type_dialysis;
    }

    public function setTypeDialysis(?string $type_dialysis): static
    {
        $this->type_dialysis = $type_dialysis;

        return $this;
    }

    public function isDrugAllergies(): ?bool
    {
        return $this->drug_allergies;
    }

    public function setDrugAllergies(?bool $drug_allergies): static
    {
        $this->drug_allergies = $drug_allergies;

        return $this;
    }

    public function getDrugAllergiePrecise(): ?string
    {
        return $this->drug_allergie_precise;
    }

    public function setDrugAllergiePrecise(?string $drug_allergie_precise): static
    {
        $this->drug_allergie_precise = $drug_allergie_precise;

        return $this;
    }

    public function getDialysisStartDate(): ?\DateTimeImmutable
    {
        return $this->dialysis_start_date;
    }

    public function setDialysisStartDate(?\DateTimeImmutable $dialysis_start_date): static
    {
        $this->dialysis_start_date = $dialysis_start_date;

        return $this;
    }

    public function getVascularAccessType(): ?string
    {
        return $this->vascular_access_type;
    }

    public function setVascularAccessType(?string $vascular_access_type): static
    {
        $this->vascular_access_type = $vascular_access_type;

        return $this;
    }

    public function getRenalFailureOther(): ?string
    {
        return $this->renal_failure_other;
    }

    public function setRenalFailureOther(?string $renal_failure_other): static
    {
        $this->renal_failure_other = $renal_failure_other;

        return $this;
    }
}
