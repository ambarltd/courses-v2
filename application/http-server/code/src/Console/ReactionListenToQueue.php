<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Galeas\Api\Service\QueueProcessor\ReactionKafkaQueueReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReactionListenToQueue extends Command
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var ReactionKafkaQueueReader
     */
    private $reactionKafkaQueueReader;

    public function __construct(
        string $environment,
        ReactionKafkaQueueReader $reactionKafkaQueueReader
    ) {
        parent::__construct();

        $this->environment = $environment;
        $this->reactionKafkaQueueReader = $reactionKafkaQueueReader;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:reaction:listen_to_queue')
                ->setDescription('Listen to queue, and react to events.');
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * No logic exeptions thrown.
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            ini_set('display_errors', '0');
            error_reporting(0);
            set_time_limit(0);

            if ('environment_test' !== $this->environment) {
                $this->reactionKafkaQueueReader->start();
            } else {
                $this->reactionKafkaQueueReader->startWithCallBackOnSuccess(
                    function () use ($output): void {
                        $output->writeln('Reaction queue has successfully processed an event or reached end of partition');
                    }
                );
            }

            return 1; // if the loop finished, something went wrong
        } catch (\Throwable $exception) {
            $output->writeln(get_class($exception));
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString());

            return 1;
        }
    }
}
