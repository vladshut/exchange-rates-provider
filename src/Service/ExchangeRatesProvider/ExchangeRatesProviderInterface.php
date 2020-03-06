<?php
declare(strict_types=1);

namespace App\Service\ExchangeRatesProvider;

use App\Entity\ExchangeRate;

interface ExchangeRatesProviderInterface
{
    /**
     * @return ExchangeRate[]
     */
    public function getDailyRates(): array;
}
