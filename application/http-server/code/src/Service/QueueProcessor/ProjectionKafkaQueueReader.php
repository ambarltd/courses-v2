<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Common\Event\EventMapper;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\TopicConf;

class ProjectionKafkaQueueReader
{
    /**
     * @var string
     */
    private $kafkaHost;

    /**
     * @var string
     */
    private $kafkaConsumerGroupId;

    /**
     * @var ProjectionQueueProcessor
     */
    private $queueProcessor;

    /**
     * @var DocumentManager
     */
    private $documentManagerToBeClearedToAvoidMemoryLeaks;

    public function __construct(
        string $kafkaHost,
        string $kafkaConsumerGroupId,
        ProjectionQueueProcessor $queueProcessor,
        DocumentManager $documentManagerToBeClearedToAvoidMemoryLeaks
    ) {
        $this->kafkaHost = $kafkaHost;
        $this->kafkaConsumerGroupId = $kafkaConsumerGroupId;
        $this->queueProcessor = $queueProcessor;
        $this->documentManagerToBeClearedToAvoidMemoryLeaks = $documentManagerToBeClearedToAvoidMemoryLeaks;
    }

    /**
     * @throws ProjectionCannotProcess
     */
    public function start(): void
    {
        try {
            $consumer = $this->createKafkaConsumer($this->kafkaConsumerGroupId);

            while (true) {
                $message = $consumer->consume(2000);
                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        $event = EventMapper::jsonEventsToEvents(
                            [$message->payload]
                        )[0];
                        $this->queueProcessor->process($event);
                        $this->documentManagerToBeClearedToAvoidMemoryLeaks->clear();
                        break;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        // No more messages; will wait for more
                        break;
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        // figure out quick rebalancing before re-enabling this
                        //throw new \Exception('Kafka Queue Reader Timeout');
                        break;
                    default:
                        throw new \Exception($message->errstr(), $message->err);
                        break;
                }
            }
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }

    /**
     * @throws \Throwable
     */
    public function startWithCallBackOnSuccess(callable $callback): void
    {
        $consumer = $this->createKafkaConsumer($this->kafkaConsumerGroupId);

        $callbackToBeCalled = true;

        while (true) {
            $message = $consumer->consume(2000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $event = EventMapper::jsonEventsToEvents(
                        [$message->payload]
                    )[0];
                    $this->queueProcessor->process($event);
                    $this->documentManagerToBeClearedToAvoidMemoryLeaks->clear();
                    if ($callbackToBeCalled) {
                        $callback();
                    }
                    $callbackToBeCalled = false;
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    if ($callbackToBeCalled) {
                        $callback();
                    }
                    $callbackToBeCalled = false;
                    // No more messages; will wait for more
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // figure out quick rebalancing before re-enabling this
                    //throw new \Exception('Kafka Queue Reader Timeout');
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    /**
     * Available as public to let others check a connection.
     *
     * @throws \Throwable
     */
    public function createKafkaConsumer(string $kafkaConsumerGroupId): KafkaConsumer
    {
        $kafkaConfiguration = new Conf();

        // Set a rebalance callback to log partition assignments (optional)
        $kafkaConfiguration
            ->setRebalanceCb(
                function (
                    KafkaConsumer $kafka,
                    int $errorCode,
                    array $partitions = null
                ): void {
                    switch ($errorCode) {
                        case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                            $kafka->assign($partitions);
                            break;

                        case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                            $kafka->assign(null);
                            break;
                        default:
                            throw new \Exception('Kafka Queue Reader Rebalancing Exception Error Code: '.$errorCode);
                    }
                }
            );

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $kafkaConfiguration->set('group.id', $kafkaConsumerGroupId);

        // Initial list of Kafka brokers
        $kafkaConfiguration->set('metadata.broker.list', $this->kafkaHost);
        $kafkaConfiguration->set('session.timeout.ms', '10000');
        $kafkaConfiguration->set('heartbeat.interval.ms', '3000');

        $topicConf = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');

        // Set the configuration to use for subscribed/assigned topics
        $kafkaConfiguration->setDefaultTopicConf($topicConf);

        $consumer = new KafkaConsumer($kafkaConfiguration);
        // todo - create topic per aggregate
        $topicNames = 'single_topic';
        $consumer->subscribe([$topicNames]);

        return $consumer;
    }
}
