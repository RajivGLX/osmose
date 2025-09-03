<?php

namespace App\Entity;

use App\Repository\StatusBookingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatusBookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class StatusBooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_booking'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'statusBookings', targetEntity: Status::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['info_booking'])]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'statusBookings', targetEntity: Booking::class, cascade: ['persist'])]
    private ?Booking $booking = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['info_booking'])]
    private ?bool $status_active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function setBooking(?Booking $booking): static
    {
        $this->booking = $booking;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isStatusActive(): ?bool
    {
        return $this->status_active;
    }

    public function setStatusActive(bool $status_active): static
    {
        $this->status_active = $status_active;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void {
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->createdAt = new \DateTimeImmutable('now', $timezone);
    }
}
