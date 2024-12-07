<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\CreditCard\Product\CommandHandler;

use Galeas\Api\BoundedContext\CreditCard\Product\Command\DefineProductCommand;
use Galeas\Api\BoundedContext\CreditCard\Product\CommandHandler\DefineProductHandler;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\InvalidPaymentCycle;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\InvalidReward;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\ProductActivated;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\ProductDeactivated;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\ProductDefined;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;

class DefineProductHandlerTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $eventStore = $this->getInMemoryEventStore();
        $handler = new DefineProductHandler($eventStore);

        $command = new DefineProductCommand();
        $command->name = 'Test Product';
        $command->interestInBasisPoints = 1_500;
        $command->annualFeeInCents = 5_000;
        $command->paymentCycle = 'monthly';
        $command->creditLimitInCents = 100_000;
        $command->maxBalanceTransferAllowedInCents = 50_000;
        $command->reward = 'cashback';
        $command->cardBackgroundHex = '#FFFFFF';

        $handler->handle($command);

        /** @var ProductActivated[]|ProductDeactivated[]|ProductDefined[] $events */
        $events = $eventStore->storedEvents();
        self::assertCount(2, $eventStore->storedEvents());
        self::assertInstanceOf(ProductDefined::class, $events[0]);
        self::assertEquals(Id::createNewByHashing('CreditCard_Product:'.$command->name), $events[0]->aggregateId());
        self::assertEquals('Test Product', $events[0]->name());
        self::assertEquals(1_500, $events[0]->interestInBasisPoints());
        self::assertEquals(5_000, $events[0]->annualFeeInCents());
        self::assertEquals('monthly', $events[0]->paymentCycle());
        self::assertEquals(100_000, $events[0]->creditLimitInCents());
        self::assertEquals(50_000, $events[0]->maxBalanceTransferAllowedInCents());
        self::assertEquals('cashback', $events[0]->reward());
        self::assertEquals('#FFFFFF', $events[0]->cardBackgroundHex());

        /** @var ProductActivated[] $events */
        $event = $eventStore->storedEvents()[1];
        self::assertCount(2, $eventStore->storedEvents());
        self::assertInstanceOf(ProductActivated::class, $events[1]);
        self::assertEquals($event->aggregateId(), $events[0]->aggregateId());

        $handler->handle($command);
        self::assertCount(2, $events);
    }

    public function testValidPaymentCycles(): void
    {
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'cashback');
        $this->handlePaymentCycleAndRewardWithDefaultValues('quarterly', 'cashback');
    }

    public function testValidRewards(): void
    {
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'cashback');
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'points');
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'no_reward');
    }

    public function testInvalidPaymentCycle(): void
    {
        $this->expectException(InvalidPaymentCycle::class);
        $this->handlePaymentCycleAndRewardWithDefaultValues('what', 'cashback');
    }

    public function testInvalidReward(): void
    {
        $this->expectException(InvalidReward::class);
        $this->handlePaymentCycleAndRewardWithDefaultValues('monthly', 'what');
    }

    public function handlePaymentCycleAndRewardWithDefaultValues(string $paymentCycle, string $reward): void
    {
        $eventStore = $this->getInMemoryEventStore();
        $handler = new DefineProductHandler($eventStore);

        $command = new DefineProductCommand();
        $command->name = 'Test Product';
        $command->interestInBasisPoints = 1_500;
        $command->annualFeeInCents = 5_000;
        $command->paymentCycle = $paymentCycle;
        $command->creditLimitInCents = 100_000;
        $command->maxBalanceTransferAllowedInCents = 50_000;
        $command->reward = $reward;
        $command->cardBackgroundHex = '#FFFFFF';

        $handler->handle($command);
        Assert::assertCount(2, $eventStore->storedEvents());
    }
}
