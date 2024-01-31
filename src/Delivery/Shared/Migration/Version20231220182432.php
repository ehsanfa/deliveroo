<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231220182432 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE `delivery_drivers` (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                status TINYINT NOT NULL DEFAULT 1,
                location POINT NOT NULL SRID 4326,
                location_updated_at DATETIME DEFAULT null,
                PRIMARY KEY(id),
                INDEX idx_status_last_update (status, location_updated_at),
                SPATIAL INDEX(location)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DROP TABLE `delivery_drivers`
        ");
    }
}
