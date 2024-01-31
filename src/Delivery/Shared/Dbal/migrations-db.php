<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;

$dsnParser = new DsnParser([
    'mysql' => 'pdo_mysql',
]);
$connectionParams = $dsnParser
    ->parse(getenv("DELIVERY_DATABASE_URL"));

return DriverManager::getConnection($connectionParams);
