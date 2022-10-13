<?php

declare(strict_types=1);

namespace App\Config;

use App\Serializer\DefaultCommandSerializer;

class Config
{
    public function __construct(
        private ExchangesMap $exchanges,
        private QueuesMap $queues,
        private BindingsMap $bindings,
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

    /** @return Exchange[] */
    public function getAllExchanges(): array
    {
        return $this->exchanges->all();
    }

    public function getAllBindings(): BindingsMap
    {
        return $this->bindings;
    }
}
