<?php

namespace App\Entity;

use App\Entity\Training;
use App\Entity\Translation\TranslationInterface;
use App\Repository\TrainingTranslationRepository;

#[ORM\Entity(repositoryClass: TrainingTranslationRepository::class)]
class TrainingTranslation implements TranslationInterface
{
    #[ORM\ManyToOne(targetEntity: Training::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private Training $translatable;

    public function getLocale(): string { /* ... */ }
    public function setLocale(string $locale): static { /* ... */ }

    public function getTranslatable(): Training
    {
        return $this->translatable;
    }

    public function setTranslatable(Training $entity): static
    {
        $this->translatable = $entity;
        return $this;
    }
}
