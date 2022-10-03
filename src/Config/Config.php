<?php

namespace App\Config;

class Config
{
    public function __construct(
        private ExchangesMap $exchanges,
        private QueuesMap $queues,
        private BindingsMap $bindings,
        private CommandPublisherConfigsMap $commandPublisherConfigs
    ) {
    }

    public function getCommandPublisherConfig(string $commandClass): CommandPublisherConfig
    {
        if (!$this->commandPublisherConfigs->exists($commandClass)) {
            $this->commandPublisherConfigs->put(
                new QueuePublishedCommandConfig($commandClass, $this->queues->get('default'))
            );
        }

        return $this->commandPublisherConfigs->get($commandClass);
    }

    public function getQueueConfig(string $name): Queue
    {
        return $this->queues->get($name);
    }

    /** @return Queue[] */
    public function getAllQueues(): array
    {
        return $this->queues->all();
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
