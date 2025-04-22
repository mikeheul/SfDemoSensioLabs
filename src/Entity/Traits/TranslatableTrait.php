<?php

namespace App\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Translation\TranslationInterface;

trait TranslatableTrait
{
    /** @var Collection<int, TranslationInterface> */
    private Collection $translations;

    public function initializeTranslations(): void
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @return Collection<int, TranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TranslationInterface $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }
        return $this;
    }

    public function getTranslation(string $locale): ?TranslationInterface
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }
        return null;
    }
}