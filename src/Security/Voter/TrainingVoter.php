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
    public const SUBSCRIBE = 'TRAINING_SUBSCRIBE';
    public const VIEW_STUDENTS = 'TRAINING_VIEW_STUDENTS';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // return in_array($attribute, [self::CREATE]) && $subject === null;
        return in_array($attribute, [self::SUBSCRIBE, self::VIEW_STUDENTS]) && $subject instanceof Training;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // return $this->security->isGranted('ROLE_ADMIN');
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::SUBSCRIBE => in_array('ROLE_STUDENT', $user->getRoles()),
            self::VIEW_STUDENTS => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_TEACHER'),
            default => false
        };
    }
}
