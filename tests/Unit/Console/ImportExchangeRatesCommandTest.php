<?php

use App\Command\ImportExchangeRatesCommand;
use App\Entity\ExchangeRate;
use App\Service\ExchangeRatesProvider\EcbExchangeRatesProvider;
use App\ValueObject\BankEnum;
use Doctrine\Common\Collections\Collection;
use Money\Currency;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ImportExchangeRatesCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $exchangeRate = new ExchangeRate(new Currency('UAH'), new Currency('USD'), 24.67, new DateTimeImmutable(), BankEnum::ECB());
        $providerMock = $this->createMock(EcbExchangeRatesProvider::class);
        $providerMock->method('getDailyRates')->willReturn([$exchangeRate]);

        $command = new ImportExchangeRatesCommand([$providerMock], $em);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Imported.', $output);

        $criteria = $exchangeRate->jsonSerialize();

        $this->assertSeeInDatabase(ExchangeRate::class, $criteria);
    }

    public function testInitialization(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $command = $application->find('app:import-exchange-rates');

        $this->assertInstanceOf(ImportExchangeRatesCommand::class, $command);
    }


    protected function assertSeeInDatabase($entity, $criteria, $message = ''): self
    {
        $count = $this->getDatabaseCount($entity, $criteria);

        $message .= sprintf(
            ' Unable to find row in database table [%s] that matched attributes [%s].', $entity, json_encode($criteria)
        );

        $this->assertGreaterThan(0, $count, $message);

        return $this;
    }

    protected function getDatabaseCount($entity, $criteria)
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $qb = $em
            ->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($entity, 'e');
        foreach ($criteria as $field => $value) {
            if ($value instanceof Collection) {
                $value = $value->toArray();
            }
            if ($value === null) {
                $qb->andWhere("e.{$field} IS NULL");
            } else if (is_array($value)) {
                array_walk($value, function (&$item) {
                    $item = "'{$item}'";
                });
                $value = implode(',', $value);
                $qb->andWhere("e.{$field} IN ({$value})");
            } else {
                $qb->andWhere("e.{$field} = :{$field}")->setParameter($field, $value);
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function assertCountInDatabase($entity, $criteria, $expectedCount, $message = ''): self
    {
        $count = $this->getDatabaseCount($entity, $criteria);

        $message .= sprintf(
            ' Unable to find row in database table [%s] that matched attributes [%s]. Message: %s', $entity, json_encode($criteria), $message
        );

        $this->assertEquals($expectedCount, $count, $message);

        return $this;
    }

    protected function assertSeeNotInDatabase($entity, $criteria): self
    {
        $count = $this->getDatabaseCount($entity, $criteria);
        $this->assertEquals(0, $count, sprintf(
            'Found row in database table [%s] that matched attributes [%s].', $entity, json_encode($criteria)
        ));

        return $this;
    }

}
