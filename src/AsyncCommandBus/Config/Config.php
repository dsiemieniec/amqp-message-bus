<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\ExchangesMap;
use Siemieniec\AsyncCommandBus\Config\Queue;
use Siemieniec\AsyncCommandBus\Config\QueuePublishedCommandConfig;
use Siemieniec\AsyncCommandBus\Config\QueuesMap;
use Siemieniec\AsyncCommandBus\Config\CommandConfig;
use Siemieniec\AsyncCommandBus\Config\CommandConfigsMap;
use Siemieniec\AsyncCommandBus\Serializer\DefaultCommandSerializer;

class Config
{
    public function __construct(
        private ExchangesMap $exchanges,
        private QueuesMap $queues,
        private CommandConfigsMap $commandConfigsMap
    ) {
    }

    public function getCommandConfig(string $commandClass): CommandConfig
    {
        if (!isset($this->commandConfigsMap[$commandClass])) {
            $this->commandConfigsMap[$commandClass] = new CommandConfig(
                $commandClass,
                DefaultCommandSerializer::class,
                new QueuePublishedCommandConfig($this->queues['default'])
            );
        }

        return $this->commandConfigsMap[$commandClass];
    }

    public function getQueueConfig(string $name): Queue
    {
        return $this->queues[$name];
    }

    public function getAllQueues(): QueuesMap
    {
        return $this->queues;
    }

    public function getAllExchanges(): ExchangesMap
    {
        return $this->exchanges;
    }
}