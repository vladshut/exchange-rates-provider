<?php
declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\BankEnum;
use DateTimeImmutable;
use Money\Currency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExchangeRateRepository")
 */
final class ExchangeRate implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed" = true})
     */
    protected $fromCurrency;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed" = true})
     */
    protected $toCurrency;

    /**
     * @ORM\Column(type="float")
     */
    protected $rate;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    protected $datetime;

    /**
     * @ORM\Column(type="string")
     */
    protected $src;

    public function __construct(Currency $from, Currency $to, float $rate, DateTimeImmutable $datetime, BankEnum $src)
    {
        $this->fromCurrency = $from->getCode();
        $this->toCurrency = $to->getCode();
        $this->rate = $rate;
        $this->datetime = $datetime;
        $this->src = $src->getValue();
    }

    /**
     * @return Currency
     */
    public function getFromCurrency(): Currency
    {
        return new Currency($this->fromCurrency);
    }

    /**
     * @return Currency
     */
    public function getToCurrency(): Currency
    {
        return new Currency($this->toCurrency);
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDatetime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    /**
     * @return mixed
     */
    public function getSrc(): BankEnum
    {
        return new BankEnum($this->src);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'fromCurrency' => $this->fromCurrency,
            'toCurrency' => $this->toCurrency,
            'rate' => $this->rate,
            'datetime' => $this->datetime,
            'src' => $this->src,
        ];
    }
}
