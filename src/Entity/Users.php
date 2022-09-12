<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $pseudo;

     /**
     * @ORM\Column(type="string", length=50)
     */
    private $img_profil;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $password;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_creation;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_derniere_modif;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $active_token;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $role = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getImgProfil(): ?string
    {
        return $this->img_profil;
    }

    public function setImgProfil(string $img_profil): self
    {
        $this->img_profil = $img_profil;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateDerniereModif(): ?\DateTimeInterface
    {
        return $this->date_derniere_modif;
    }

    public function setDateDerniereModif(?\DateTimeInterface $date_derniere_modif): self
    {
        $this->date_derniere_modif = $date_derniere_modif;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }

    public function getActiveToken(): ?string
    {
        return $this->active_token;
    }

    public function setActiveToken(?string $active_token): self
    {
        $this->active_token = $active_token;

        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
    public function getRoles()
    {
        return array('Administrateur', 'Utilisateur');
    }
    public function eraseCredentials(){}
    public function getUsername(){}
    public function getSalt(): ?string {
        return null;
    }
    public function getUserIdentifier() {
        return (string) $this->email;
    }
}
