<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\DBAL\Exception;
use Galeas\Api\Service\DBMigration\DBMigration as DBMigrationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBMigration extends Command
{
    private DBMigrationService $dbMigrationService;

    public function __construct(
        DBMigrationService $dbMigrationService
    ) {
        parent::__construct();

        $this->dbMigrationService = $dbMigrationService;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:dbs:updates')
                ->setDescription('Create Event Store and Hydrate Projection Collections')
            ;
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->dbMigrationService->createEventStoreAndHydrateProjections(true);

        return 0;
    }
}
