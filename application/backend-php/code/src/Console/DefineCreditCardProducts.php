<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Galeas\Api\BoundedContext\CreditCard\Product\Command\DefineProductCommand;
use Galeas\Api\BoundedContext\CreditCard\Product\CommandHandler\DefineProductHandler;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\InvalidPaymentCycle;
use Galeas\Api\BoundedContext\CreditCard\Product\Event\InvalidReward;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefineCreditCards extends Command
{
    private DefineProductHandler $defineProductHandler;

    public function __construct(
        DefineProductHandler $defineProductHandler
    ) {
        parent::__construct();

        $this->defineProductHandler = $defineProductHandler;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:define_credit_card_products')
                ->setDescription('Defines Credit Card Products')
            ;
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * @throws EventStoreCannotRead|EventStoreCannotWrite|InvalidPaymentCycle|InvalidReward|NoRandomnessAvailable
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $defineStarterCard = new DefineProductCommand();
        $defineStarterCard->name = 'Starter';
        $defineStarterCard->interestInBasisPoints = 1_200;
        $defineStarterCard->annualFeeInCents = 5_000;
        $defineStarterCard->paymentCycle = 'monthly';
        $defineStarterCard->creditLimitInCents = 50_000;
        $defineStarterCard->maxBalanceTransferAllowedInCents = 0;
        $defineStarterCard->reward = 'none';
        $defineStarterCard->cardBackgroundHex = '#7fffd4';
        $this->defineProductHandler->handle($defineStarterCard);

        $definePlatinumCard = new DefineProductCommand();
        $definePlatinumCard->name = 'Platinum';
        $definePlatinumCard->interestInBasisPoints = 300;
        $definePlatinumCard->annualFeeInCents = 50_000;
        $definePlatinumCard->paymentCycle = 'monthly';
        $definePlatinumCard->creditLimitInCents = 500_000;
        $definePlatinumCard->maxBalanceTransferAllowedInCents = 100_000;
        $definePlatinumCard->reward = 'points';
        $definePlatinumCard->cardBackgroundHex = '#E5E4E2';
        $this->defineProductHandler->handle($definePlatinumCard);

        return 0;
    }
}
