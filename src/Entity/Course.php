<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @var Collection<int, Shedule>
     */
    #[ORM\OneToMany(targetEntity: Shedule::class, mappedBy: 'course', orphanRemoval: true)]
    private Collection $shedules;

    public function __construct()
    {
        $this->shedules = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Shedule>
     */
    public function getShedules(): Collection
    {
        return $this->shedules;
    }

    public function addShedule(Shedule $shedule): static
    {
        if (!$this->shedules->contains($shedule)) {
            $this->shedules->add($shedule);
            $shedule->setCourse($this);
        }

        return $this;
    }

    public function removeShedule(Shedule $shedule): static
    {
        if ($this->shedules->removeElement($shedule)) {
            // set the owning side to null (unless already changed)
            if ($shedule->getCourse() === $this) {
                $shedule->setCourse(null);
            }
        }

        return $this;
    }
}
