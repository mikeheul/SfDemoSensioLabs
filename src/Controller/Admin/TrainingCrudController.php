<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Training;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\String\Slugger\SluggerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TrainingCrudController extends AbstractCrudController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public static function getEntityFqcn(): string
    {
        return Training::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Training && empty($entityInstance->getSlug())) {
            $entityInstance->setSlug(strtolower($this->slugger->slug($entityInstance->getTitle())));
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('status')
                ->setLabel('Status')
                ->setTemplatePath('admin/custom_status_field.html.twig'),
            TextField::new('title'),
            TextareaField::new('description'),
            DateField::new('startDate'),
            DateField::new('endDate'),
            MoneyField::new('price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->setCustomOption('maxLength', 10),
            AssociationField::new('courses')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', true)
                ->setFormTypeOption('by_reference', false)
                ->setRequired(false)
                ->setHelp('Select the courses related to this training')
                ->setLabel('Courses')
                ->setColumns('col-12 col-md-6 col-lg-4'),
            AssociationField::new('trainees')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', true) 
                ->setLabel('Enrolled Users') 
                // ->setTemplatePath('admin/users_in_training.html.twig') // Facultatif : si vous voulez personnaliser l'affichage
                ->setSortable(false), 
        ];
    }
}
