<?php
declare(strict_types=1);


use App\Entity\ExchangeRate;
use App\Service\ExchangeRatesProvider\CbrExchangeRatesProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CbrExchangeRatesProviderTest extends KernelTestCase
{
    /**
     * @throws GuzzleException
     */
    public function testGetDailyRates(): void
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn(
            new Response(200, [], '
                <ValCurs Date="07.03.2020" name="Foreign Currency Market">
                    <Valute ID="R01010">
                      <NumCode>036</NumCode>
                      <CharCode>AUD</CharCode>
                      <Nominal>10</Nominal>
                      <Name>Австралийский доллар</Name>
                      <Value>44,8181</Value>
                    </Valute>
                </ValCurs>
            ')
        );

        $provider = new CbrExchangeRatesProvider($clientMock);
        $rates = $provider->getDailyRates();

        $this->assertCount(1, $rates);
        /** @var ExchangeRate $rate */
        $rate = $rates[0];

        $this->assertEquals('AUD', $rate->getToCurrency()->getCode());
        $this->assertEquals('RUB', $rate->getFromCurrency()->getCode());
        $this->assertEquals(1 / 4.48181, $rate->getRate());
        $this->assertEquals('2020-03-07', $rate->getDatetime()->format('Y-m-d'));
    }
}
