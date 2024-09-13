<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Galeas\Api\Service\QueueProcessor\ProjectionKafkaQueueReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectionListenToQueue extends Command
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var ProjectionKafkaQueueReader
     */
    private $projectionKafkaQueueReader;

    public function __construct(
        string $environment,
        ProjectionKafkaQueueReader $projectionKafkaQueueReader
    ) {
        parent::__construct();

        $this->environment = $environment;
        $this->projectionKafkaQueueReader = $projectionKafkaQueueReader;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:projection:listen_to_queue')
                ->setDescription('Listen to queue, and project events into read side database.');
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
                $this->projectionKafkaQueueReader->start();
            } else {
                $this->projectionKafkaQueueReader->startWithCallBackOnSuccess(
                    function () use ($output): void {
                        $output->writeln('Projection queue has successfully processed an event or reached end of partition');
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
