<?php
declare(strict_types=1);

namespace App\Service\ExchangeRatesProvider;

use App\Entity\ExchangeRate;
use App\ValueObject\BankEnum;
use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Money\Currency;
use SimpleXMLElement;

class EcbExchangeRatesProvider implements ExchangeRatesProviderInterface
{
    private Client $httpClient;
    private $baseUrl = 'https://www.ecb.europa.eu/';
    private $baseCurrency;
    private $bank;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->baseCurrency = new Currency('EUR');
        $this->bank = BankEnum::ECB();
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getDailyRates(): array
    {
        $url = $this->baseUrl . '/stats/eurofxref/eurofxref-daily.xml';
        $response = $this->httpClient->request('GET', $url);

        $content = $response->getBody()->getContents();

        $data = (new SimpleXMLElement($content))->Cube->Cube;
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', (string)$data['time']);

        $exchangeRates = [];

        foreach ($data->Cube as $currency) {
            $currencyCode = (string)$currency['currency'];
            $rate = (float)$currency['rate'];

            $exchangeRates[] = new ExchangeRate($this->baseCurrency, new Currency($currencyCode), $rate, $dateTime, $this->bank);
        }

        return $exchangeRates;
    }
}
