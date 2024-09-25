<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCardProduct\Product\CommandHandler;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Command\DefineProductCommand;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\CommandHandler\DefineProductHandler;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\InvalidPaymentCycle;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\InvalidReward;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\Event\ProductDefined;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;

class DefineProductHandlerTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $eventStore = $this->getInMemoryEventStore();
        $handler = new DefineProductHandler($eventStore);

        $command = new DefineProductCommand();
        $command->productIdentifierForAggregateIdHash = 'some-hash';
        $command->name = 'Test Product';
        $command->interestInBasisPoints = 1500;
        $command->annualFeeInCents = 5000;
        $command->paymentCycle = 'monthly';
        $command->creditLimitInCents = 100000;
        $command->maxBalanceTransferAllowedInCents = 50000;
        $command->reward = 'cashback';
        $command->cardBackgroundHex = '#FFFFFF';

        $handler->handle($command);

        /** @var ProductDefined[] $events */
        $events = $eventStore->storedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProductDefined::class, $events[0]);
        $this->assertEquals('Test Product', $events[0]->name());
        $this->assertEquals(1500, $events[0]->interestInBasisPoints());
        $this->assertEquals(5000, $events[0]->annualFeeInCents());
        $this->assertEquals('monthly', $events[0]->paymentCycle());
        $this->assertEquals(100000, $events[0]->creditLimitInCents());
        $this->assertEquals(50000, $events[0]->maxBalanceTransferAllowedInCents());
        $this->assertEquals('cashback', $events[0]->reward());
        $this->assertEquals('#FFFFFF', $events[0]->cardBackgroundHex());

        $handler->handle($command);
        $this->assertCount(1, $events);
    }

    public function testValidPaymentCycles()
    {
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'cashback');
        $this->handlePaymentCycleAndRewardWithDefaultValues('quarterly', 'cashback');
    }

    public function testValidRewards()
    {
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'cashback');
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'points');
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'no_reward');
    }

    public function testInvalidPaymentCycle()
    {
        $this->expectException(InvalidPaymentCycle::class);
        $this->handlePaymentCycleAndRewardWithDefaultValues('what', 'cashback');
    }

    public function testInvalidReward()
    {
        $this->expectException(InvalidReward::class);
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'what');
    }

    public function handlePaymentCycleAndRewardWithDefaultValues(string $paymentCycle, string $reward): void
    {
        $eventStore = $this->getInMemoryEventStore();
        $handler = new DefineProductHandler($eventStore);

        $command = new DefineProductCommand();
        $command->productIdentifierForAggregateIdHash = 'some-hash';
        $command->name = 'Test Product';
        $command->interestInBasisPoints = 1500;
        $command->annualFeeInCents = 5000;
        $command->paymentCycle = $paymentCycle;
        $command->creditLimitInCents = 100000;
        $command->maxBalanceTransferAllowedInCents = 50000;
        $command->reward = $reward;
        $command->cardBackgroundHex = '#FFFFFF';

        $handler->handle($command);
        Assert::assertCount(1, $eventStore->storedEvents());
    }
}