### Running database migrations on test env
```shell
DELIVERY_DATABASE_URL="mysql://test:test@mysql:3306/deliveroo_test?serverVersion=8.0.32&charset=utf8mb4" ./vendor/bin/doctrine-migrations migrations:migrate --configuration src/Delivery/Shared/Migration/migrations.yaml --db-configuration src/Delivery/Shared/Dbal/migrations-db.php
```