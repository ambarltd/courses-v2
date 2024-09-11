<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Galeas\Api\Service\Queue\KafkaQueue;
use Galeas\Api\Service\QueueProcessor\ProjectionKafkaQueueReader;
use Galeas\Api\Service\QueueProcessor\ReactionKafkaQueueReader;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RdKafka\Metadata;
use RdKafka\Producer;
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

    /**
     * @var KafkaQueue
     */
    private $kafkaQueue;

    /**
     * @var ReactionKafkaQueueReader
     */
    private $reactionKafkaQueueReader;

    /**
     * @var ProjectionKafkaQueueReader
     */
    private $projectionKafkaQueueReader;

    public function __construct(
        string $environment,
        string $apiDomain,
        DocumentManager $reactionDocumentManager,
        DocumentManager $projectionDocumentManager,
        SQLEventStoreConnection $sqlEventStoreConnection,
        KafkaQueue $kafkaQueue,
        ReactionKafkaQueueReader $reactionKafkaQueueReader,
        ProjectionKafkaQueueReader $projectionKafkaQueueReader
    ) {
        parent::__construct();

        $this->environment = $environment;
        $this->apiDomain = $apiDomain;
        $this->reactionDocumentManager = $reactionDocumentManager;
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->sqlEventStoreConnection = $sqlEventStoreConnection;
        $this->kafkaQueue = $kafkaQueue;
        $this->reactionKafkaQueueReader = $reactionKafkaQueueReader;
        $this->projectionKafkaQueueReader = $projectionKafkaQueueReader;
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
                $this->sqlEventStoreConnection->getConnection()->connect();

            if (true !== $eventStoreStatus) {
                throw new \RuntimeException('Cannot connect to event store');
            }
            $output->writeln('EventStore DB OK');

            // PRODUCER
            $kafkaProducer = $this->kafkaQueue->createProducer();
            $kafkaProducer->getOutQLen();
            $kafkaStatus = $kafkaProducer->getMetadata(true, null, 50);

            if (!$kafkaStatus instanceof Metadata) {
                throw new \RuntimeException('Cannot connect to kafka');
            }
            $output->writeln('Producer OK');

            // PROJECTION CONSUMER
            $consumer = $this->projectionKafkaQueueReader->createKafkaConsumer('test_connections_projection');
            $message = $consumer->consume(10000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                case RD_KAFKA_RESP_ERR__PARTITION_EOF: // No more messages
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                default:
                    throw new \RuntimeException('Cannot connect to kafka: '.$message->errstr());
                    break;
            }
            $output->writeln('Projection Consumer OK');

            // REACTION CONSUMER
            $consumer = $this->reactionKafkaQueueReader->createKafkaConsumer('test_connections_projection');
            $message = $consumer->consume(10000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                case RD_KAFKA_RESP_ERR__PARTITION_EOF: // No more messages
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                default:
                throw new \RuntimeException('Cannot connect to kafka: '.$message->errstr());
                    break;
            }
            $output->writeln('Reaction Consumer OK');
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
