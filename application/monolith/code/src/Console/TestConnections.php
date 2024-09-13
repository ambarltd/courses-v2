<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestConnections extends Command
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $apiDomain;

    /**
     * @var DocumentManager
     */
    private $reactionDocumentManager;

    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    /**
     * @var SQLEventStoreConnection
     */
    private $sqlEventStoreConnection;


    public function __construct(
        string $environment,
        string $apiDomain,
        DocumentManager $reactionDocumentManager,
        DocumentManager $projectionDocumentManager,
        SQLEventStoreConnection $sqlEventStoreConnection,
    ) {
        parent::__construct();

        $this->environment = $environment;
        $this->apiDomain = $apiDomain;
        $this->reactionDocumentManager = $reactionDocumentManager;
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->sqlEventStoreConnection = $sqlEventStoreConnection;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:dev:test_connections')
                ->setDescription('Test connections to databases and queues.');
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
            // API
            $url = $this->apiDomain.'/schema/list';
            $client = new Client();

            $response = $client->request(
                'GET',
                $url,
                [
                    'http_errors' => false,
                    'verify' => false,
                ]
            );
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('Cannot connect to api via '.$url);
            }
            $output->writeln('Nginx and PHP-API responding via '.$url);

            // PROJECTION DB
            $this->projectionDocumentManager->getClient()->listDatabases();
            $output->writeln('Projection DB OK');

            // REACTION DB
            $this->reactionDocumentManager->getClient()->listDatabases();
            $output->writeln('Reaction DB OK');

            // EVENT STORE
            $eventStoreStatus = $this->sqlEventStoreConnection->getConnection()->isConnected() ||
                null !== $this->sqlEventStoreConnection->getConnection()->executeQuery("SELECT 1");

            if (true !== $eventStoreStatus) {
                throw new \RuntimeException('Cannot connect to event store');
            }
            $output->writeln('EventStore DB OK');

        } catch (GuzzleException $throwable) {
            $output->writeln('Error, could not verify all connections.');
            $output->writeln($throwable->getMessage());

            return 1;
        } catch (\Throwable $throwable) {
            $output->writeln('Error, could not verify all connections.');
            $output->writeln($throwable->getMessage());

            return 1;
        }

        $output->writeln('All OK. Connections checked.');

        return 0;
    }
}
