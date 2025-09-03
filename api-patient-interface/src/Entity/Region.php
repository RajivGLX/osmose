<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user', 'info_center', 'info_region'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['info_user', 'info_center', 'info_region'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Center::class, orphanRemoval: true, fetch: 'LAZY')]
    private Collection $centers;

    public function __construct()
    {
        $this->centers = new ArrayCollection();
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
            $center->setRegionId($this);
        }

        return $this;
    }

    public function removeCenter(Center $center): static
    {
        if ($this->centers->removeElement($center)) {
            // set the owning side to null (unless already changed)
            if ($center->getRegionId() === $this) {
                $center->setRegionId(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->name;
    }
}
