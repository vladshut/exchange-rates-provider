<?php
declare(strict_types=1);


use App\Controller\ExchangeRatesController;
use App\Service\Exchanger;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class ExchangeRatesControllerTest extends KernelTestCase
{
    public function testShow(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        /** @var ExchangeRatesController $controller */
        $controller = $kernel->getContainer()->get(ExchangeRatesController::class);

        $request = new Request([
            'from' => 'UAH',
            'to' => 'USD',
            'amount' => '245.89',
        ]);

        $exchangerMock = $this->createMock(Exchanger::class);
        $exchangerMock->method('exchange')->willReturn(
            new Money(57867, new Currency('USD'))
        );

        $response = $controller->show($request, $exchangerMock);

        $this->assertEquals('578.67', $response->getContent());
    }
}
