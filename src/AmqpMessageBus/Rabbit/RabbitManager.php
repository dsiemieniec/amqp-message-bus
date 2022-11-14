<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Psr\Log\LoggerInterface;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Config\Connection;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\Queue;

class RabbitManager
{
    public function __construct(
        private Config $config,
        private LoggerInterface $logger
    ) {
    }

    public function declareAll(): void
    {
        $this->declareAllQueues();
        $this->declareAllExchanges();
    }

    private function declareAllQueues(): void
    {
        foreach ($this->config->getAllQueues() as $queue) {
            $this->declareQueue($queue);
        }
    }

    private function declareQueue(Queue $queue): void
    {
        $this->getRabbitConnection($queue->getConnection())->declareQueue($queue);
    }

    private function declareAllExchanges(): void
    {
        foreach ($this->config->getAllExchanges() as $exchange) {
            $this->declareExchange($exchange);
        }
    }

    private function declareExchange(Exchange $exchange): void
    {
        $connection = $this->getRabbitConnection($exchange->getConnection());
        $connection->declareExchange($exchange);
        foreach ($exchange->getQueueBindings() as $queueBinding) {
            $connection->declareQueue($queueBinding->getQueue());
            $connection->bindQueue($exchange, $queueBinding);
        }
    }

    private function getRabbitConnection(Connection $connectionConfig): RabbitConnection
    {
        return new RabbitConnection($connectionConfig, $this->logger);
    }
}
