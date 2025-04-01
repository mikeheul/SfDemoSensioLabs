<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['training:read']],
    denormalizationContext: ['groups' => ['training:write']]
)]
class Training
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training_detail'])]
    private ?string $title = null;
    
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['training:read', 'training_detail'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['training:read', 'training_detail'])]
    private ?\DateTimeInterface $startDate = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['training:read', 'training_detail'])]
    private ?\DateTimeInterface $endDate = null;
    
    #[ORM\Column]
    #[Groups(['training:read', 'training_detail'])]
    private ?float $price = null;

    /**
     * @var Collection<int, Course>
     */
    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'trainings')]
    #[ApiProperty(readable: true)]
    #[Groups(['training:read'])]
    private Collection $courses;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'trainings')]
    #[Groups(["training_detail_trainees"])]
    private Collection $trainees;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->trainees = new ArrayCollection();
        $this->status = 'draft';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        $this->courses->removeElement($course);

        return $this;
    }
    
    /**
     * @return Collection<int, User>
     */
    public function getTrainees(): Collection
    {
        return $this->trainees;
    }
    
    public function addTrainee(User $trainee): static
    {
        if (!$this->trainees->contains($trainee)) {
            $this->trainees->add($trainee);
        }
        
        return $this;
    }
    
    public function removeTrainee(User $trainee): static
    {
        $this->trainees->removeElement($trainee);
        
        return $this;
    }

    public function __toString(): string
    {
        return $this->title.' - ' . $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d');
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
