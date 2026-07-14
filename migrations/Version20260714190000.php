<?php

declare(strict_types=1);

namespace DoctrineMigrations\Contract;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260714190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Baseline schema for contract module from s.controleonline.com";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('CREATE TABLE IF NOT EXISTS `contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_model_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alter_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `contract_file_id` int(11) DEFAULT NULL,
  `doc_key` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contract_model_id` (`contract_model_id`),
  KEY `status_id` (`status_id`),
  KEY `contract_file_id` (`contract_file_id`),
  KEY `provider_id` (`provider_id`) USING BTREE,
  KEY `client_id` (`client_id`),
  CONSTRAINT `contract_ibfk_5` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `contract_ibfk_6` FOREIGN KEY (`contract_model_id`) REFERENCES `model` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `contract_ibfk_7` FOREIGN KEY (`provider_id`) REFERENCES `people` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `contract_ibfk_8` FOREIGN KEY (`contract_file_id`) REFERENCES `files` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `contract_ibfk_9` FOREIGN KEY (`client_id`) REFERENCES `people` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('CREATE TABLE IF NOT EXISTS `contract_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `people_id` int(11) NOT NULL COMMENT \'pessoa física\',
  `people_type` enum(\'Beneficiary\',\'Witness\',\'Contractor\') CHARACTER SET utf8 NOT NULL,
  `contract_percentage` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contract_id` (`contract_id`),
  KEY `people_id` (`people_id`),
  CONSTRAINT `contract_people_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contract_people_ibfk_2` FOREIGN KEY (`people_id`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10952 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DROP TABLE IF EXISTS `contract_people`');
        $this->addSql('DROP TABLE IF EXISTS `contract`');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}
