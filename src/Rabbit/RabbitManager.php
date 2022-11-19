<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Psr\Log\LoggerInterface;
use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Config\Connection;
use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\Queue;
use Siemieniec\AmqpMessageBus\Exception\BindingDeclarationException;
use Siemieniec\AmqpMessageBus\Exception\ExchangeDeclarationException;
use Siemieniec\AmqpMessageBus\Exception\QueueDeclarationException;
use Throwable;

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
        try {
            $this->getRabbitConnection($queue->getConnection())->declareQueue($queue);
        } catch (Throwable $exception) {
            throw new QueueDeclarationException($queue, $exception);
        }
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
        try {
            $connection->declareExchange($exchange);
        } catch (Throwable $exception) {
            throw new ExchangeDeclarationException($exchange, $exception);
        }

        foreach ($exchange->getQueueBindings() as $queueBinding) {
            try {
                $connection->declareQueue($queueBinding->getQueue());
            } catch (Throwable $exception) {
                throw new QueueDeclarationException($queueBinding->getQueue(), $exception);
            }
            try {
                $connection->bindQueue($exchange, $queueBinding);
            } catch (Throwable $exception) {
                throw new BindingDeclarationException($exchange, $queueBinding, $exception);
            }
        }
    }

    private function getRabbitConnection(Connection $connectionConfig): RabbitConnection
    {
        return new RabbitConnection($connectionConfig, $this->logger);
    }
}
