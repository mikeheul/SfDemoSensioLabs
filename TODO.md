Projet Demo SensioLabs

Symfony, Tailwind, Mailtrap
Training
Course
User

Training : title, description, startDate, endDate, price, teacher, courses
Course : name, description

✅ -- CRUD training, course 
✅ -- Auth : role student, teacher, admin
🟧 -- S'inscrire à un cours --> Mail de confirmation (EventSubscriber / EventListener)
✅ -- Créer des services pour récupérer les formations, cours, students
✅ -- API training, course (API Platform)
✅ -- Back office Easy Admin (collectiontype courses dans training ?)
✅ -- Tests unitaires / fonctionnels / intégration
-- Workflow pour l'inscription à un cours (admin valide l'inscription)
✅ -- Voters
✅ -- Subscriber : inscription / désinscription d'un stagiaire à une formation --> email de confirmation
-- Fixtures pôur alimenter la base de données ?


Steps :
✅ -- Création projet : symfony new SfDemoSensioLabs --webapp
✅ -- Installation des dépendances : 
    ✅ - composer require easycorp/easyadmin-bundle
    ✅ - composer require api
✅ -- Création dashboard admin : symfony console make:admin:dashboard
✅ -- Installation / configuration de TailwindCSS 
✅ -- Modification du fichier .env pour la base de données (MySQL)
✅ -- Création de la base de données : symfony console doctrine:database:create (d:d:c)
✅ -- Création des entités : symfony console make:entity
✅ -- Migration : symfony console make:migration + symfony console doctrine:migrations:migrate
✅ -- Création des controllers : symfony console make:controller
✅ -- Création des formulaires : symfony console make:form
✅ -- Utiliser des partials pour les formulaires (training et course)
✅ -- Ajouter une route globale pour les controllers (training et course)
✅ -- Affichage détail d'un cours
✅ -- Affichage détail d'une formation