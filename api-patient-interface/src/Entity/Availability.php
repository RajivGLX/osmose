<?php

namespace App\Entity;

use App\Repository\AvailabilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
class Availability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['availability', 'info_booking'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['availability', 'info_booking'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Groups(['availability', 'info_booking'])]
    private ?int $available_place = null;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Center $center = null;

    #[ORM\Column]
    #[Groups(['availability'])]
    private ?int $reserved_place = null;

    #[ORM\OneToMany(mappedBy: 'availability', targetEntity: Booking::class)]
    #[Groups(['availability'])]
    private Collection $bookings;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['availability', 'info_booking'])]
    private ?Slots $slot = null;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAvailablePlace(): ?int
    {
        return $this->available_place;
    }

    public function setAvailablePlace(int $available_place): static
    {
        $this->available_place = $available_place;

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

    public function getReservedPlace(): ?int
    {
        return $this->reserved_place;
    }

    public function setReservedPlace(int $reserved_place): static
    {
        $this->reserved_place = $reserved_place;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setAvailability($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAvailability() === $this) {
                $booking->setAvailability(null);
            }
        }

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
}
