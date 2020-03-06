<?php

namespace App\Command;

use App\Service\ExchangeRatesProvider\ExchangeRatesProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

final class ImportExchangeRatesCommand extends Command
{
    protected static $defaultName  = 'app:import-exchange-rates';

    /** @var ExchangeRatesProviderInterface[] */
    protected $exchangeRatesProviders;
    protected EntityManagerInterface $em;

    protected function configure(): void
    {
        $this->setDescription('Imports exchange rates from source.');
    }

    public function __construct($exchangeRatesProviders, EntityManagerInterface $em)
    {
        Assert::allImplementsInterface($exchangeRatesProviders, ExchangeRatesProviderInterface::class);

        $this->exchangeRatesProviders = $exchangeRatesProviders;
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rates = [];

        foreach ($this->exchangeRatesProviders as $exchangeRatesProvider) {
            $rates = array_merge($rates, $exchangeRatesProvider->getDailyRates());
        }


        foreach ($rates as $rate) {
            $this->em->persist($rate);
        }

        $this->em->flush();

        $output->writeln('Imported.');

        return 0;
    }
}
