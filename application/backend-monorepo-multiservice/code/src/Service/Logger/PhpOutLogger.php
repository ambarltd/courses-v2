<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class PhpOutLogger implements LoggerInterface
{
    private Logger $logger;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $handler = new StreamHandler('php://stdout', Level::Debug);
        $this->logger = new Logger('PhpOutLogger', [$handler]);
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @param mixed $level
     *
     * @throws \InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        try {
            $isValidString = \is_string($level)
                && \in_array($level, ['alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'], true);
            $isLevel = $level instanceof Level;
            if (!$isValidString || !$isLevel) {
                throw new \InvalidArgumentException('Invalid log level');
            }

            $this->logger->log($level, $message, $context);
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException('Invalid log level');
        }
    }
}
