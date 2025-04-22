<?php

namespace App\Entity\Translation;

interface TranslationInterface
{
    public function getLocale(): string;
    public function setLocale(string $locale): self;
}
