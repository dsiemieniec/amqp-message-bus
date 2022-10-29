<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

use Siemieniec\AmqpMessageBus\Serializer\DefaultMessageSerializer;

class Config
{
    public function __construct(
        private ExchangesMap $exchanges,
        private QueuesMap $queues,
        private MessageConfigsMap $messageConfigsMap
    ) {
    }

    public function getMessageConfig(string $messageClass): MessageConfig
    {
        if (!isset($this->messageConfigsMap[$messageClass])) {
            $this->messageConfigsMap[$messageClass] = new MessageConfig(
                $messageClass,
                DefaultMessageSerializer::class,
                new QueuePublishedMessageConfig($this->queues['default'])
            );
        }

        return $this->messageConfigsMap[$messageClass];
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
