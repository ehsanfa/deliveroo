<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Scorer;

use App\Delivery\Driver\Scorer;
use App\Delivery\DriverRate\DriverList;

readonly class MultipleScorer implements Scorer
{
    /**
     * @var Scorer[]
     */
    private array $scorers;

    public function __construct(Scorer ...$scorer)
    {
        $this->scorers = $scorer;
    }

    public function score(DriverList $driverList): DriverList
    {
        foreach ($this->scorers as $scorer) {
            $driverList = $scorer->score($driverList);
        }
        return $driverList;
    }
}