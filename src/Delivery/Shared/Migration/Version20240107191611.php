<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240107191611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE `delivery_trips` (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                driver_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin,
                status TINYINT NOT NULL DEFAULT 1,
                source POINT NOT NULL SRID 4326,
                destination POINT NOT NULL SRID 4326,
                PRIMARY KEY(id),
                INDEX idx_status (status)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DROP TABLE `delivery_trips`
        ");
    }
}
