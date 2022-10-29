<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Serializer;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Config\Config;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelopeInterface;

final class Serializer
{
    public function __construct(
        private CommandSerializerRegistry $registry,
        private Config $config
    ) {
    }

    public function serialize(
        object $command,
        ?CommandProperties $properties = null
    ): MessageEnvelopeInterface {
        return $this->getSerializer(\get_class($command))
            ->serialize($command, $properties ?: new CommandProperties());
    }

    public function deserialize(MessageEnvelopeInterface $envelope): object
    {
        return $this->getSerializer($envelope->getCommandClass())->deserialize($envelope);
    }

    private function getSerializer(string $commandClass): CommandSerializerInterface
    {
        return $this->registry->getSerializer(
            $this->config->getCommandConfig($commandClass)->getSerializerClass()
        );
    }
}
