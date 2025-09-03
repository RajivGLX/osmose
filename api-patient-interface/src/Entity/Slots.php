<?php

namespace App\Entity;

use App\Repository\SlotsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SlotsRepository::class)]
class Slots
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking','availability', 'info_booking'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['booking','availability', 'info_booking'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'slot', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\OneToMany(mappedBy: 'slot', targetEntity: Availability::class)]
    private Collection $availabilities;

    #[ORM\Column]
    private ?bool $first = null;

    #[ORM\Column]
    private ?bool $second = null;

    #[ORM\Column]
    private ?bool $third = null;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setSlot($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getSlot() === $this) {
                $booking->setSlot(null);
            }
        }

        return $this;
    }

    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setSlot($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): static
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getSlot() === $this) {
                $availability->setSlot(null);
            }
        }

        return $this;
    }

    public function isFirst(): ?bool
    {
        return $this->first;
    }

    public function setFirst(bool $first): static
    {
        $this->first = $first;

        return $this;
    }

    public function isSecond(): ?bool
    {
        return $this->second;
    }

    public function setSecond(bool $second): static
    {
        $this->second = $second;

        return $this;
    }

    public function isThird(): ?bool
    {
        return $this->third;
    }

    public function setThird(bool $third): static
    {
        $this->third = $third;

        return $this;
    }
}
