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
        if (!$this->commandConfigsMap->has($commandClass)) {
            $this->commandConfigsMap->put(
                new CommandConfig(
                    $commandClass,
                    DefaultCommandSerializer::class,
                    new QueuePublishedCommandConfig($this->queues['default'])
                )
            );
        }

        return $this->commandConfigsMap->get($commandClass);
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

    /** @return Binding[] */
    public function getAllBindings(): array
    {
        return $this->bindings->all();
    }
}
