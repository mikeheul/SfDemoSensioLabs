<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Training;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('email'),
            ChoiceField::new('roles')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Teacher' => 'ROLE_TEACHER',
                    'Student' => 'ROLE_STUDENT',
                ])
                ->allowMultipleChoices()
                ->setHelp('Laissez vide si vous ne voulez pas attribuer de rôles spécifiques. ROLE_USER est toujours attribué par défaut.'),
            AssociationField::new('trainings')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', true)
                ->setFormTypeOption('by_reference', false)
                ->setRequired(false)
                ->setHelp('Select the trainings related to this trainee')
                ->setLabel('Trainings')
        ];

        return $fields;
    }

    private function getAvailableTrainingsField(): AssociationField
    {
        $user = $this->getContext()->getEntity()->getInstance(); // Get the current user entity

        // Get all trainings
        $allTrainings = $this->getDoctrine()->getRepository(Training::class)->findAll();
        
        // Get trainings the user is already enrolled in
        $enrolledTrainings = $user->getTrainings();

        // Filter out the trainings the user is already enrolled in
        $availableTrainings = array_diff($allTrainings, $enrolledTrainings);

        // Use AssociationField for available trainings
        return AssociationField::new('availableTrainings')
            ->setChoices($availableTrainings)
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('expanded', true)
            ->setHelp('Select the trainings you want to enroll this user in.');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        // After saving the user, we need to handle the addition of selected trainings
        if ($entityInstance instanceof User) {
            $selectedTrainingIds = $this->request->request->get('availableTrainings');
            $trainings = $this->getDoctrine()->getRepository(Training::class)->findByIds($selectedTrainingIds);

            foreach ($trainings as $training) {
                $entityInstance->addTraining($training);
            }

            $entityManager->flush();
        }
    }
}
