<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_booking', 'status_admin'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_booking', 'status_admin'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_booking', 'status_admin'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_booking', 'status_admin'])]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_booking', 'status_admin'])]
    private ?string $bg_color = null;

    #[ORM\Column]
    private ?bool $status_wait = null;

    #[ORM\Column]
    private ?bool $status_confirm = null;

    #[ORM\Column]
    private ?bool $status_denied = null;

    #[ORM\Column]
    private ?bool $status_contact = null;

    #[ORM\Column]
    private ?bool $status_canceled = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: StatusBooking::class)]
    private Collection $statusBookings;

    #[ORM\Column(nullable: true)]
    private ?bool $status_finish = null;

    public function __construct()
    {
        $this->booking = new ArrayCollection();
        $this->statusBookings = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function isStatusDefault(): ?bool
    {
        return $this->status_wait;
    }

    public function setStatusDefault(bool $status_wait): static
    {
        $this->status_wait = $status_wait;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBooking(): Collection
    {
        return $this->booking;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getBgColor(): ?string
    {
        return $this->bg_color;
    }

    public function setBgColor(?string $bg_color): static
    {
        $this->bg_color = $bg_color;

        return $this;
    }

    public function isStatusConfirm(): ?bool
    {
        return $this->status_confirm;
    }

    public function setStatusConfirm(bool $status_confirm): static
    {
        $this->status_confirm = $status_confirm;

        return $this;
    }

    public function isStatusDenied(): ?bool
    {
        return $this->status_denied;
    }

    public function setStatusDenied(bool $status_denied): static
    {
        $this->status_denied = $status_denied;

        return $this;
    }

    public function isStatusContact(): ?bool
    {
        return $this->status_contact;
    }

    public function setStatusContact(bool $status_contact): static
    {
        $this->status_contact = $status_contact;

        return $this;
    }

    public function isStatusCanceled(): ?bool
    {
        return $this->status_canceled;
    }

    public function setStatusCanceled(bool $status_canceled): static
    {
        $this->status_canceled = $status_canceled;

        return $this;
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
            $statusBooking->setStatus($this);
        }

        return $this;
    }

    public function removeStatusBooking(StatusBooking $statusBooking): static
    {
        if ($this->statusBookings->removeElement($statusBooking)) {
            // set the owning side to null (unless already changed)
            if ($statusBooking->getStatus() === $this) {
                $statusBooking->setStatus(null);
            }
        }

        return $this;
    }

    public function isStatusFinish(): ?bool
    {
        return $this->status_finish;
    }

    public function setStatusFinish(?bool $status_finish): static
    {
        $this->status_finish = $status_finish;

        return $this;
    }
}
