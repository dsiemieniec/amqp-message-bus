<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\CommandInterface;
use App\Config\Config;
use App\Rabbit\Message\MessageEnvelopeInterface;

final class Serializer
{
    public function __construct(
        private CommandSerializerRegistry $registry,
        private Config $config
    ) {
    }

    public function serialize(CommandInterface $command): MessageEnvelopeInterface
    {
        return $this->getSerializer(\get_class($command))->serialize($command);
    }

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface
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
