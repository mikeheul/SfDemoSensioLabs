<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Course;
use App\Entity\Training;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Exemple de formations
        $trainingData = [
            'Développement Web Frontend' => [
                'HTML & CSS',
                'JavaScript',
                'ReactJS',
                'VueJS',
                'Responsive Design',
            ],
            'Développement Web Backend' => [
                'PHP & MySQL',
                'Node.js',
                'API Rest',
                'Framework Laravel',
                'Symfony',
            ],
            'Data Science et Machine Learning' => [
                'Python pour Data Science',
                'Introduction au Machine Learning',
                'Deep Learning avec TensorFlow',
                'Visualisation de données avec Matplotlib',
            ],
            'Cybersécurité et Sécurisation des Systèmes' => [
                'Introduction à la Cybersécurité',
                'Sécurisation des Réseaux',
                'Hacking Éthique',
                'Sécurité des Applications Web',
            ],
            'Marketing Digital' => [
                'SEO',
                'Publicité Facebook Ads',
                'Google Analytics',
                'Stratégie de contenu',
            ],
        ];

        foreach ($trainingData as $trainingName => $courses) {
            // Créer la formation
            $training = new Training();
            $training->setTitle($trainingName);
            $training->setDescription("Formation complète sur $trainingName.");

            // Définir les dates de début et de fin
            $startDate = $faker->dateTimeBetween('+1 week', '+2 months');
            $endDate = (clone $startDate)->modify('+1 month');
            $training->setStartDate($startDate);
            $training->setEndDate($endDate);

            // Générer un prix aléatoire pour la formation (par exemple entre 100 et 500 EUR)
            $price = $faker->randomFloat(2, 100, 500);
            $training->setPrice($price);

            foreach ($courses as $courseName) {
                // Créer un cours
                $course = new Course();
                $course->setName($courseName);
                $course->setDescription("Cours sur $courseName dans le cadre de la formation $trainingName");

                // Ajouter le cours à la formation
                $training->addCourse($course);

                // Sauvegarder le cours
                $manager->persist($course);
            }

            // Sauvegarder la formation
            $manager->persist($training);
        }

        // Sauvegarder toutes les données en base
        $manager->flush();
    }
}
