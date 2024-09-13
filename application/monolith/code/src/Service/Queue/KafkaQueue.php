<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Queue;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventMapper;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Primitive\PrimitiveTransformation\Hash\IntUnder2000;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\TopicConf;

class KafkaQueue implements Queue
{
    /**
     * @var string
     */
    private $kafkaHost;

    public function __construct(string $kafkaHost)
    {
        $this->kafkaHost = $kafkaHost;
    }

    /**
     * How many kafka partitions?
     *
     * 2,000,000 messages per second (extremely down the line target)  / 10,000 messages per second (slow single partition throughput)
     * 2,000,000 messages per second (extremely down the line target) / 1,000 messages per second (extremely slow consumption)
     *
     * max (200, 2000) = 2,000 partitions
     *
     * @see https://www.confluent.io/blog/how-to-choose-the-number-of-topicspartitions-in-a-kafka-cluster/
     *
     * NOTE: CURRENTLY ONE PARTITION - TODO change deployment to be scalable once in production.
     * CONFIGURATION NEEDS TO BE SUCH THAT CONSUMERS CAN READ, AND PRODUCERS ARE GIVEN PARTITIONS, LEADERS, ETC.
     *
     * {@inheritdoc}
     */
    public function enqueue(Event $event): void
    {
        try {
            $kafkaProducer = $this->createProducer();

            $serializedEvent = EventMapper::eventsToSerializedEvents([$event])[0];

            // todo - create topic per aggregate
            $topicName = 'single_topic';
            $topicConfig = new TopicConf();
            $topicConfig->set('message.timeout.ms', '200');
            $topic = $kafkaProducer->newTopic($topicName);

            $partition = IntUnder2000::hash($serializedEvent->aggregateId());

            $topic->produce(
                RD_KAFKA_PARTITION_UA,
                0,
                EventMapper::eventsToJsonEvents(
                    [$event]
                )[0]
            );

            while ($kafkaProducer->getOutQLen() > 0) {
                $kafkaProducer->poll(1);
            }
        } catch (\Throwable $exception) {
            throw new QueuingFailure($exception);
        }
    }

    /**
     * @throws \Exception
     *
     * @see https://github.com/arnaud-lb/php-rdkafka#performance--low-latency-settings for performance
     * @see https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md for full list of settings
     */
    public function createProducer(): Producer
    {
        $conf = new Conf();

        $conf->set('socket.timeout.ms', '10'); // or socket.blocking.max.ms, depending on librdkafka version
        $conf->set('socket.blocking.max.ms', '10');
        $conf->set('queue.buffering.max.ms', '10');
        $conf->set('queue.buffering.max.messages', '1');

        if (function_exists('pcntl_sigprocmask')) {
            pcntl_sigprocmask(SIG_BLOCK, [SIGIO]);
            $conf->set('internal.termination.signal', strval(SIGIO));
        } else {
            throw new \Exception('Enable this for performance!');
        }

        $kafkaProducer = new Producer($conf);
        // deprecated $kafkaProducer->setLogLevel(LOG_DEBUG);
        $kafkaProducer->addBrokers($this->kafkaHost);

        return $kafkaProducer;
    }
}
