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

final class CbrExchangeRatesProvider implements ExchangeRatesProviderInterface
{
    private Client $httpClient;
    private $baseUrl = 'https://www.cbr.ru';
    private $baseCurrency;
    private $bank;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->baseCurrency = new Currency('RUB');
        $this->bank = BankEnum::CBR();
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getDailyRates(): array
    {
        $url = $this->baseUrl . '/scripts/XML_daily.asp';
        $response = $this->httpClient->request('GET', $url);

        $content = $response->getBody()->getContents();

        $data = new SimpleXMLElement($content);
        $dateTime = DateTimeImmutable::createFromFormat('d.m.Y', (string)$data['Date']);

        $exchangeRates = [];

        foreach ($data as $currency) {
            $currencyCode = (string)$currency->CharCode;
            $nominal = (int)$currency->Nominal;
            $rate = 1 / ((float)str_replace(',', '.', $currency->Value) / $nominal);

            $exchangeRates[] = new ExchangeRate($this->baseCurrency, new Currency($currencyCode), $rate, $dateTime, $this->bank);
        }

        return $exchangeRates;
    }
}
