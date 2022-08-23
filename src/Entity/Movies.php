<?php

namespace App\Entity;

use App\Repository\MoviesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MoviesRepository::class)
 */
class Movies
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

     /**
     * @ORM\Column(type="string", length=255)
     */
    private $affiche;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $genre;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_sortie;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $realisateurs;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acteurs;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $synopsis;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $dispo_plateforme;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getAffiche(): ?string
    {
        return $this->affiche;
    }

    public function setAffiche(string $affiche): self
    {
        $this->affiche = $affiche;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->date_sortie;
    }

    public function setDateSortie(?\DateTimeInterface $date_sortie): self
    {
        $this->date_sortie = $date_sortie;

        return $this;
    }

    public function getRealisateurs(): ?string
    {
        return $this->realisateurs;
    }

    public function setRealisateurs(?string $realisateurs): self
    {
        $this->realisateurs = $realisateurs;

        return $this;
    }

    public function getActeurs(): ?string
    {
        return $this->acteurs;
    }

    public function setActeurs(string $acteurs): self
    {
        $this->acteurs = $acteurs;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getDispoPlateforme(): ?string
    {
        return $this->dispo_plateforme;
    }

    public function setDispoPlateforme(?string $dispo_plateforme): self
    {
        $this->dispo_plateforme = $dispo_plateforme;

        return $this;
    }
}
