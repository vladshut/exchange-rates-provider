<?php
declare(strict_types=1);


use App\Entity\ExchangeRate;
use App\Service\ExchangeRatesProvider\EcbExchangeRatesProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EcbExchangeRatesProviderTest extends KernelTestCase
{
    /**
     * @throws GuzzleException
     */
    public function testGetDailyRates(): void
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')->willReturn(
            new Response(200, [], '
                <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
                    <gesmes:subject>Reference rates</gesmes:subject>
                    <gesmes:Sender>
                      <gesmes:name>European Central Bank</gesmes:name>
                    </gesmes:Sender>
                    <Cube>
                      <Cube time="2020-03-06">
                        <Cube currency="USD" rate="1.1336"/>
                      </Cube>
                    </Cube>
                </gesmes:Envelope>
            ')
        );

        $provider = new EcbExchangeRatesProvider($clientMock);
        $rates = $provider->getDailyRates();

        $this->assertCount(1, $rates);
        /** @var ExchangeRate $rate */
        $rate = $rates[0];

        $this->assertEquals('USD', $rate->getToCurrency()->getCode());
        $this->assertEquals('EUR', $rate->getFromCurrency()->getCode());
        $this->assertEquals(1.1336, $rate->getRate());
        $this->assertEquals('2020-03-06', $rate->getDatetime()->format('Y-m-d'));
    }
}
