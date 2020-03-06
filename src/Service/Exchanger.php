<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Exception\AmountNotConverted;
use App\Repository\ExchangeRateRepository;
use App\ValueObject\BankEnum;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Money\Currency;
use Money\Money;

class Exchanger
{
    private ExchangeRateRepository $repository;
    private BankEnum $source;

    public function __construct(ExchangeRateRepository $exchangeRateRepository, string $source) {
        $this->source = new BankEnum($source);
        $this->repository = $exchangeRateRepository;
    }

    /**
     * @param Money $amount
     * @param Currency $toCurrency
     * @return Money
     * @throws AmountNotConverted
     */
    public function exchange(Money $amount, Currency $toCurrency): Money
    {
        $fromCurrencyCode = $amount->getCurrency()->getCode();
        $toCurrencyCode = $toCurrency->getCode();

        $exchangeRates = $this->repository->getRatesForCurrentDate($this->source);

        if (empty($exchangeRates)) {
            throw new AmountNotConverted();
        }

        $graph = new Graph();

        /** @var ExchangeRate $exchangeRate */
        foreach ($exchangeRates as $exchangeRate) {
            $fromVertex = $graph->createVertex($exchangeRate->getFromCurrency()->getCode(), true);
            $toVertex = $graph->createVertex($exchangeRate->getToCurrency()->getCode(), true);

            $edge = $fromVertex->createEdgeTo($toVertex);
            $edge->setWeight($exchangeRate->getRate());

            $reverseEdge = $toVertex->createEdgeTo($fromVertex);
            $reverseEdge->setWeight(1 / $exchangeRate->getRate());
        }


        if (!$graph->hasVertex($fromCurrencyCode) || !$graph->hasVertex($toCurrencyCode)) {
            throw new AmountNotConverted();
        }

        $fromVertex = $graph->getVertex($fromCurrencyCode);
        $toVertex = $graph->getVertex($toCurrencyCode);

        $alg = new Dijkstra($fromVertex);
        $path = $alg->getEdgesTo($toVertex);

        if (empty($path)) {
            throw new AmountNotConverted();
        }

        $newAmount = (float)$amount->getAmount();

        foreach ($path as $edge) {
            $newAmount *= $edge->getWeight();
        }

        return new Money((int)$newAmount, new Currency($toCurrencyCode));
    }
}
