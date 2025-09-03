<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_user', 'info_booking'])]
    private ?int $id = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['info_user'])]
    private array $roles = [];

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Ne doit pas être vide')]
    #[Assert\Email(message: 'Email invalide')]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $valid = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $deleted = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['info_user', 'info_booking'])]
    private ?bool $admin = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info_user', 'info_booking'])]
    private ?string $lastname = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['info_user'])]
    private ?Patient $patient = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['info_user'])]
    private ?Administrator $administrator = null;

    #[ORM\Column]
    #[Groups(['info_user', 'info_booking'])]
    private ?\DateTimeImmutable $create_at = null;

    public function __construct()
    {
        $this->create_at = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }


    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getColorCode(): string
    {
        $code = dechex(crc32($this->getFirstname()));
        $code = substr($code, 0, 6);

        return '#' . $code;
    }

    public function getAvatarUrl(): string
    {
        return "https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=" . $this->firstname . '+' . $this->lastname;
    }


    public function __toString(): string
    {
        return "$this->lastname ($this->id)";
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }


    public function isEqualTo(UserInterface $user): bool
    {
        if ($user instanceof User) {
            return $this->isValid() &&
                !$this->isDeleted() &&
                $this->getPassword() == $user->getPassword() &&
                $this->getFirstname() == $user->getFirstname() &&
                $this->getLastname() == $user->getLastname() &&
                $this->getEmail() == $user->getEmail();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getSalt(): ?string
    {
        //not used here
        return null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }


    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        // unset the owning side of the relation if necessary
        if ($patient === null && $this->patient !== null) {
            $this->patient->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($patient !== null && $patient->getUser() !== $this) {
            $patient->setUser($this);
        }

        $this->patient = $patient;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): static
    {
        // unset the owning side of the relation if necessary
        if ($administrator === null && $this->administrator !== null) {
            $this->administrator->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($administrator !== null && $administrator->getUser() !== $this) {
            $administrator->setUser($this);
        }

        $this->administrator = $administrator;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->create_at = new \DateTimeImmutable('now', $timezone);
    }
}
