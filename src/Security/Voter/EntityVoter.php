<?php

namespace App\Security\Voter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class EntityVoter extends Voter
{
    public const CREATE = 'ENTITY_CREATE';

    private array $supportedEntities = [
        'App\Entity\Training',
        'App\Entity\Course',
    ];

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE]) && ($subject === null || in_array(get_class($subject), $this->supportedEntities));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
