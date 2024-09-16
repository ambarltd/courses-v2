<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEventStore extends Command
{
    /**
     * @var DocumentManager
     */
    private DocumentManager $reactionDocumentManager;

    /**
     * @var DocumentManager
     */
    private DocumentManager $projectionDocumentManager;

    /**
     * @var SQLEventStoreConnection
     */
    private SQLEventStoreConnection $sqlEventStoreConnection;

    public function __construct(
        DocumentManager $reactionDocumentManager,
        DocumentManager $projectionDocumentManager,
        SQLEventStoreConnection $sqlEventStoreConnection,
    ) {
        parent::__construct();

        $this->reactionDocumentManager = $reactionDocumentManager;
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->sqlEventStoreConnection = $sqlEventStoreConnection;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:create_event_store')
                ->setDescription('Create Event Store');
        } catch (\Throwable $throwable) {
            return;
        }
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $this->sqlEventStoreConnection->getConnection()
                ->executeStatement("
CREATE TABLE IF NOT EXISTS `event` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`event_id` varchar(56) COLLATE utf8_unicode_ci NOT NULL UNIQUE,
`aggregate_id` varchar(56) COLLATE utf8_unicode_ci NOT NULL,
`aggregate_version` bigint(20) unsigned NOT NULL,
`causation_id` varchar(56) COLLATE utf8_unicode_ci NULL,
`correlation_id` varchar(56) COLLATE utf8_unicode_ci NULL,
`event_occurred_on` datetime(6) NOT NULL,
`event_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
`json_payload` longtext COLLATE utf8_unicode_ci NOT NULL,
`json_metadata` longtext COLLATE utf8_unicode_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE INDEX aggregate_id ON event (aggregate_id);
");
        } catch (\Exception $exception) {
            return 0;
        }

        return 0;
    }
}
