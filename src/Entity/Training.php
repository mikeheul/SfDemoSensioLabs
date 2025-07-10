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
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['training:read']],
    denormalizationContext: ['groups' => ['training:write']]
)]
class Training
{
    public const PLACE_DRAFT = 'draft';
    public const PLACE_REVIEW = 'review';
    public const PLACE_CONFIRMED = 'confirmed';

    public const TRANSITION_TO_REVIEW = 'to_review';
    public const TRANSITION_TO_CONFIRMED = 'to_confirmed';
    public const TRANSITION_TO_DRAFT = 'to_draft';

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_EXPERT = 'expert';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['training:read', 'training_detail'])]
    private ?string $titleEn = null;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['training:read', 'training_detail'])]
    private ?string $descriptionEn = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['training:read', 'training_detail'])]
    private ?\DateTimeInterface $startDate = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThanOrEqual(propertyPath: "startDate")]
    #[Groups(['training:read', 'training_detail'])]
    private ?\DateTimeInterface $endDate = null;
    
    #[ORM\Column]
    #[Assert\Positive]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_fr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_es = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_fr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_es = null;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->trainees = new ArrayCollection();
        $this->status = self::PLACE_DRAFT;
        $this->level = self::LEVEL_BEGINNER;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitle(string $titleEn): static
    {
        $this->titleEn = $titleEn;

        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $descriptionEn): static
    {
        $this->descriptionEn = $descriptionEn;

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
    
    public function getStatus(): ?string
    {
        return $this->status;
    }
    
    public function setStatus(?string $status): static
    {
        $this->status = $status;
        
        return $this;
    }

    public function getMarking(): ?string
    {
        return $this->status; 
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;
    }

    public function __toString(): string
    {
        return $this->title.' - ' . $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d');
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTitleFr(): ?string
    {
        return $this->title_fr;
    }

    public function setTitleFr(?string $title_fr): static
    {
        $this->title_fr = $title_fr;

        return $this;
    }

    public function getTitleEs(): ?string
    {
        return $this->title_es;
    }

    public function setTitleEs(?string $title_es): static
    {
        $this->title_es = $title_es;

        return $this;
    }

    public function getDescriptionFr(): ?string
    {
        return $this->description_fr;
    }

    public function setDescriptionFr(?string $description_fr): static
    {
        $this->description_fr = $description_fr;

        return $this;
    }

    public function getDescriptionEs(): ?string
    {
        return $this->description_es;
    }

    public function setDescriptionEs(?string $description_es): static
    {
        $this->description_es = $description_es;

        return $this;
    }
}
