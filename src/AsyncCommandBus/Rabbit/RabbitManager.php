<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use Siemieniec\AsyncCommandBus\Config\Config;
use Siemieniec\AsyncCommandBus\Config\Connection;
use Siemieniec\AsyncCommandBus\Config\Exchange;
use Siemieniec\AsyncCommandBus\Config\Queue;
use Siemieniec\AsyncCommandBus\Rabbit\RabbitConnection;

class RabbitManager
{
    public function __construct(
        private Config $config
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
        return new RabbitConnection($connectionConfig);
    }
}
