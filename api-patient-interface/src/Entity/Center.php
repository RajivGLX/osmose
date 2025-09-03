<?php

namespace App\Entity;

use App\Repository\CenterRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CenterRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Center
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user', 'info_center', 'region', 'info_booking'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_center', 'region', 'info_booking'])]
    private ?string $name = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $email = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $phone = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $url = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $band = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $latitude_longitude = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $slug = null;
    
    #[ORM\Column(length: 500)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_center', 'info_booking'])]
    private ?string $city = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_center', 'info_booking'])]
    private ?float $zipcode = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_center'])]
    private ?int $place_available = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $information = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_center'])]
    private ?bool $different_facturation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $address_facturation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?string $city_facturation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?float $zipcode_facturation = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_center'])]
    private ?bool $active = null;
    
    #[ORM\Column(nullable: true)]
    #[Groups(['info_user', 'info_center'])]
    private ?array $center_day = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_center'])]
    private ?bool $deleted = null;

    #[ORM\ManyToOne(inversedBy: 'centers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['info_user','info_booking', 'info_center'])]
    private ?Region $region = null;

    #[ORM\OneToMany(mappedBy: 'center', targetEntity: Booking::class)]
    private Collection $bookings;

    #[ORM\OneToMany(mappedBy: 'center', targetEntity: Patient::class)]
    private Collection $patients;

    #[ORM\ManyToMany(targetEntity: Administrator::class, inversedBy: 'centers')]
    private Collection $administrator;

    #[ORM\OneToMany(mappedBy: 'center', targetEntity: Availability::class)]
    private Collection $availabilities;

    #[ORM\OneToMany(mappedBy: 'center', targetEntity: Facture::class)]
    #[Groups(['info_user'])]
    private Collection $factures;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->patients = new ArrayCollection();
        $this->administrator = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->factures = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBand(): ?string
    {
        return $this->band;
    }

    public function setBand(?string $band): static
    {
        $this->band = $band;

        return $this;
    }

    public function getLatitudeLongitude(): ?string
    {
        return $this->latitude_longitude;
    }

    public function setLatitudeLongitude(?string $latitude_longitude): static
    {
        $this->latitude_longitude = $latitude_longitude;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region_id): static
    {
        $this->region = $region_id;

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
            $booking->setCenter($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getCenter() === $this) {
                $booking->setCenter(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->name;
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

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponibles pour ce centre grace au reservation connu
     * dans la BDD
     * return array Un tableau d'objets DateTime représentant les jours d'occupation
     */
    public function getNotAvailableDays()
    {
//        $notAvailableDays = [];
//
//        foreach ($this->bookings as $booking) {
//            // Calculer les jours qui se trouvent entre la date d'arrivée et de départ
//            $resultat = range(
//                $booking->getStartDate()->getTimestamp(),
//                $booking->getEndDate()->getTimestamp(),
//                24 * 60 * 60
//            );
//
//            $days = array_map(function ($dayTimestamp) {
//                return new \DateTime(date('Y-m-d', $dayTimestamp));
//            }, $resultat);
//
//            $notAvailableDays = array_merge($notAvailableDays, $days);
//        }
//
//        return $notAvailableDays;
    }


    #Permet d'initialiser le slug
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug(): void
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->name);
        }
    }

    /**
     * @return Collection<int, Patient>
     */
    public function getPatients(): Collection
    {
        return $this->patients;
    }

    public function addPatient(Patient $patient): static
    {
        if (!$this->patients->contains($patient)) {
            $this->patients->add($patient);
            $patient->setCenter($this);
        }

        return $this;
    }

    public function removePatient(Patient $patient): static
    {
        if ($this->patients->removeElement($patient)) {
            // set the owning side to null (unless already changed)
            if ($patient->getCenter() === $this) {
                $patient->setCenter(null);
            }
        }

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?float
    {
        return $this->zipcode;
    }

    public function setZipcode(float $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection<int, Administrator>
     */
    public function getAdministrator(): Collection
    {
        return $this->administrator;
    }

    public function addAdministrator(Administrator $administrator): static
    {
        if (!$this->administrator->contains($administrator)) {
            $this->administrator->add($administrator);
        }

        return $this;
    }

    public function removeAdministrator(Administrator $administrator): static
    {
        $this->administrator->removeElement($administrator);

        return $this;
    }

    public function getPlaceAvailable(): ?int
    {
        return $this->place_available;
    }

    public function setPlaceAvailable(int $place_available): static
    {
        $this->place_available = $place_available;

        return $this;
    }

    /**
     * @return Collection<int, Availability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setCenter($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): static
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getCenter() === $this) {
                $availability->setCenter(null);
            }
        }

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): static
    {
        $this->information = $information;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isDifferentFacturation(): ?bool
    {
        return $this->different_facturation;
    }

    public function setDifferentFacturation(bool $different_facturation): static
    {
        $this->different_facturation = $different_facturation;

        return $this;
    }

    public function getAddressFacturation(): ?string
    {
        return $this->address_facturation;
    }

    public function setAddressFacturation(?string $address_facturation): static
    {
        $this->address_facturation = $address_facturation;

        return $this;
    }

    public function getCityFacturation(): ?string
    {
        return $this->city_facturation;
    }

    public function setCityFacturation(?string $city_facturation): static
    {
        $this->city_facturation = $city_facturation;

        return $this;
    }

    public function getZipcodeFacturation(): ?float
    {
        return $this->zipcode_facturation;
    }

    public function setZipcodeFacturation(?float $zipcode_facturation): static
    {
        $this->zipcode_facturation = $zipcode_facturation;

        return $this;
    }

    public function getCenterDay(): ?array
    {
        return $this->center_day;
    }

    public function setCenterDay(?array $centerDay): static
    {
        $this->center_day = $centerDay;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setCenter($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getCenter() === $this) {
                $facture->setCenter(null);
            }
        }

        return $this;
    }
}
