<?php

namespace App\Security\Voter;

use App\Entity\Training;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class TrainingVoter extends Voter
{
    public const CREATE = 'TRAINING_CREATE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE]) && $subject === null;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
