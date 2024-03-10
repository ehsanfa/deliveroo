<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240121125944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE `delivery_driver_reservation` (
                driver_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                trip_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                PRIMARY KEY(driver_id, trip_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DROP TABLE `delivery_driver_reservation`
        ");
    }
}