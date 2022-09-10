<?php

namespace App\Rabbit;

use App\Config\Binding;
use App\Config\Config;
use App\Config\Connection;
use App\Config\Exchange;
use App\Config\Queue;

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
        $this->declareAllBindings();
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
        $this->getRabbitConnection($exchange->getConnection())->declareExchange($exchange);
    }

    private function declareAllBindings(): void
    {
        foreach ($this->config->getAllBindings() as $binding) {
            $this->declareBinding($binding);
        }
    }

    private function declareBinding(Binding $binding): void
    {
        $this->getRabbitConnection($binding->getConnection())->bindQueue($binding);
    }

    private function getRabbitConnection(Connection $connectionConfig): RabbitConnection
    {
        return new RabbitConnection($connectionConfig);
    }
}
