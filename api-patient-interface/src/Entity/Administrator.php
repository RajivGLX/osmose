<?php

namespace App\Entity;

use App\Repository\AdministratorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdministratorRepository::class)]
class Administrator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_user'])]
    private ?string $service = null;

    #[ORM\OneToOne(inversedBy: 'administrator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Center::class, mappedBy: 'administrator')]
    #[Groups(['info_user'])]
    private Collection $centers;

    public function __construct()
    {
        $this->centers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): static
    {
        $this->service = $service;

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
     * @return Collection<int, Center>
     */
    public function getCenters(): Collection
    {
        return $this->centers;
    }

    public function addCenter(Center $center): static
    {
        if (!$this->centers->contains($center)) {
            $this->centers->add($center);
            $center->addAdministrator($this);
        }

        return $this;
    }

    public function removeCenter(Center $center): static
    {
        if ($this->centers->removeElement($center)) {
            $center->removeAdministrator($this);
        }

        return $this;
    }
}
