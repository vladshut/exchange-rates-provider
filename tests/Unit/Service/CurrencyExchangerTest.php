<?php
declare(strict_types=1);


use App\Entity\ExchangeRate;
use App\Exception\AmountNotConverted;
use App\Repository\ExchangeRateRepository;
use App\Service\Exchanger;
use App\ValueObject\BankEnum;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CurrencyExchangerTest extends KernelTestCase
{
    /**
     * @throws AmountNotConverted
     */
    public function testExchange(): void
    {
        $datetime = new DateTimeImmutable();
        $src = BankEnum::ECB();

        $rates = [
            new ExchangeRate(new Currency('USD'), new Currency('EUR'), 30, $datetime, $src),
            new ExchangeRate(new Currency('RUB'), new Currency('EUR'), 0.5, $datetime, $src),
            new ExchangeRate(new Currency('RUB'), new Currency('UAH'), 70, $datetime, $src),
        ];

        $repositoryMock = $this->createMock(ExchangeRateRepository::class);
        $repositoryMock->method('getRatesForCurrentDate')->willReturn($rates);

        $exchanger = new Exchanger($repositoryMock, $src->getValue());

        $result = $exchanger->exchange(new Money(50000, new Currency('USD')), new Currency('UAH'));

        $this->assertEquals('UAH', $result->getCurrency()->getCode());
        $this->assertSame(50000 * 30 / 0.5 * 70, (float)$result->getAmount());
    }
}
