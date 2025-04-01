<?php

namespace App\Serializer\Normalizer;

use App\Entity\Course;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class CourseNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * Normaliser l'objet Course en tableau.
     *
     * @param Course $object L'objet à normaliser
     * @param string $format Format de la sérialisation (par exemple 'json')
     * @param array  $context Contexte de la sérialisation
     *
     * @return array Tableau contenant les données de l'objet Course normalisées
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!$object instanceof Course) {
            // Si l'objet n'est pas de type Course, on ne fait rien.
            return [];
        }

        return [
            'id' => $object->getId(),
            'title' => $object->getName(),
            'description' => $object->getDescription()
        ];
    }

    /**
     * Vérifie si cet objet peut être normalisé.
     *
     * @param mixed $data
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Course;
    }
}
