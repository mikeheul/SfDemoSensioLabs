<?php

namespace App\Controller\Admin;

use App\Entity\Training;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TrainingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Training::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextEditorField::new('description'),
            DateField::new('startDate'),
            DateField::new('endDate'),
            MoneyField::new('price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->setCustomOption('maxLength', 10),
            AssociationField::new('shedules')
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('expanded', true)
                ->setFormTypeOption('attr', ['class' => 'form-check-input'])
                ->setCustomOption('maxLength', 10),
        ];
    }
}
