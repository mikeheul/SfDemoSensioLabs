<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710174838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, message VARCHAR(255) NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_course (training_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_2572A8D6BEFD98D1 (training_id), INDEX IDX_2572A8D6591CC992 (course_id), PRIMARY KEY(training_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE training_user (training_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8209910ABEFD98D1 (training_id), INDEX IDX_8209910AA76ED395 (user_id), PRIMARY KEY(training_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_course ADD CONSTRAINT FK_2572A8D6BEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_course ADD CONSTRAINT FK_2572A8D6591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_user ADD CONSTRAINT FK_8209910ABEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_user ADD CONSTRAINT FK_8209910AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shedule DROP FOREIGN KEY FK_E7771B51BEFD98D1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shedule DROP FOREIGN KEY FK_E7771B51591CC992
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE shedule
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course ADD slug VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training ADD status VARCHAR(255) NOT NULL, ADD slug VARCHAR(255) DEFAULT NULL, ADD level VARCHAR(255) DEFAULT NULL, ADD title_fr VARCHAR(255) DEFAULT NULL, ADD title_es VARCHAR(255) DEFAULT NULL, ADD description_fr LONGTEXT DEFAULT NULL, ADD description_es LONGTEXT DEFAULT NULL, CHANGE title title_en VARCHAR(255) NOT NULL, CHANGE description description_en LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD is_active TINYINT(1) NOT NULL, ADD first_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE shedule (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_E7771B51591CC992 (course_id), INDEX IDX_E7771B51BEFD98D1 (training_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shedule ADD CONSTRAINT FK_E7771B51BEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shedule ADD CONSTRAINT FK_E7771B51591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_course DROP FOREIGN KEY FK_2572A8D6BEFD98D1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_course DROP FOREIGN KEY FK_2572A8D6591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_user DROP FOREIGN KEY FK_8209910ABEFD98D1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_user DROP FOREIGN KEY FK_8209910AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_course
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course DROP slug
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training ADD title VARCHAR(255) NOT NULL, DROP title_en, DROP status, DROP slug, DROP level, DROP title_fr, DROP title_es, DROP description_fr, DROP description_es, CHANGE description_en description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP is_active, DROP first_name, DROP last_name
        SQL);
    }
}
