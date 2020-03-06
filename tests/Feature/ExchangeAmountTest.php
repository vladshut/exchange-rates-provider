<?php
declare(strict_types=1);


use App\Entity\ExchangeRate;
use App\ValueObject\BankEnum;
use Money\Currency;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExchangeAmountTest extends WebTestCase
{

    /** @var  KernelBrowser $client */
    static $client;


    public function testExchange(): void
    {
        static::$client = static::createClient();

        $fromCurrency = new Currency('USD');
        $toCurrency = new Currency('UAH');
        $datetime = new DateTimeImmutable();
        $src = new BankEnum(self::$container->getParameter('app.exchange_rates_src'));
        $rate = 25;
        $amount = 500;

        $exchangeRate = new ExchangeRate($fromCurrency, $toCurrency, $rate, $datetime, $src);

        $repo = self::$container->get('doctrine.orm.entity_manager');

        $repo->persist($exchangeRate);
        $repo->flush();

        static::$client->request('GET', '/exchange', ['from' => $toCurrency->getCode(), 'to' => $fromCurrency->getCode(), 'amount' => $amount]);

        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($amount / $rate, (float)$response->getContent());
    }
}
