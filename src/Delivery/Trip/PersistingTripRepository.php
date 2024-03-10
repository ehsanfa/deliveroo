<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

interface PersistingTripRepository
{
    public function find(Id $id): ?Trip;

    public function create(Trip $trip): void;

    public function update(Trip $trip): void;

    public function delete(Trip $trip): void;

    public function nextIdentity(): Id;
}