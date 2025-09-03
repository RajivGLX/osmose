<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking', 'availability', 'info_booking'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking', 'info_booking'])]
    private ?Center $center = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking','availability', 'info_booking'])]
    private ?\DateTimeInterface $dateReserve = null;

    #[ORM\Column]
    #[Groups(['booking', 'info_booking'])]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['booking', 'info_booking'])]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'booking')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking', 'info_booking'])]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking', 'info_booking'])]
    private ?Availability $availability = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking', 'availability'])]
    private ?Slots $slot = null;

    #[ORM\Column(length: 255)]
    #[Groups(['booking', 'info_booking'])]
    private ?string $reason = null;

    #[ORM\OneToMany(mappedBy: 'booking', targetEntity: StatusBooking::class)]
    #[Groups(['booking','availability', 'info_booking'])]
    private Collection $statusBookings;

    private Collection $statuses;

    public function __construct()
    {
        $this->statuses = new ArrayCollection();
        $this->statusBookings = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateReserve(): ?\DateTimeInterface
    {
        return $this->dateReserve;
    }

    public function setDateReserve(\DateTimeInterface $dateReserve): static
    {
        $this->dateReserve = $dateReserve;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }

    public function setAvailability(?Availability $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getSlot(): ?Slots
    {
        return $this->slot;
    }

    public function setSlot(?Slots $slot): static
    {
        $this->slot = $slot;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    /**
     * @return Collection<int, StatusBooking>
     */
    public function getStatusBookings(): Collection
    {
        return $this->statusBookings;
    }

    public function addStatusBooking(StatusBooking $statusBooking): static
    {
        if (!$this->statusBookings->contains($statusBooking)) {
            $this->statusBookings->add($statusBooking);
            $statusBooking->setBooking($this);
        }

        return $this;
    }

    public function removeStatusBooking(StatusBooking $statusBooking): static
    {
        if ($this->statusBookings->removeElement($statusBooking)) {
            // set the owning side to null (unless already changed)
            if ($statusBooking->getBooking() === $this) {
                $statusBooking->setBooking(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void {
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->createAt = new \DateTimeImmutable('now', $timezone);
    }
}
