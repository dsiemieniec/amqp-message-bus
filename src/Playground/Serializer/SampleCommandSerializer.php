<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\AnotherSimpleCommand;
use Siemieniec\AmqpMessageBus\Command\Properties\CommandProperties;
use Siemieniec\AmqpMessageBus\Exception\DeserializationException;
use Siemieniec\AmqpMessageBus\Exception\SerializationException;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelope;
use Siemieniec\AmqpMessageBus\Rabbit\MessageEnvelopeInterface;
use DateTimeImmutable;
use Siemieniec\AmqpMessageBus\Serializer\CommandSerializerInterface;

class SampleCommandSerializer implements CommandSerializerInterface
{
    public function serialize(object $command, CommandProperties $properties): MessageEnvelopeInterface
    {
        /** @var AnotherSimpleCommand $command */

        $body = \json_encode([
            'first_text' => $command->getFirstText(),
            'second_text' => $command->getSecondText(),
            'date_time' => $command->getDateTime()->format(DATE_ISO8601)
        ]);
        if ($body === false) {
            throw new SerializationException(\json_last_error_msg());
        }

        return new MessageEnvelope($body, \get_class($command), $properties);
    }

    public function deserialize(MessageEnvelopeInterface $envelope): AnotherSimpleCommand
    {
        $data = \json_decode((string)$envelope->getBody(), true);

        $dateTime = DateTimeImmutable::createFromFormat(DATE_ISO8601, $data['date_time']);
        if ($dateTime === false) {
            throw new DeserializationException(
                \sprintf('Invalid date format. Expected %s given  %s', DATE_ISO8601, $data['date_time'])
            );
        }

        return new AnotherSimpleCommand(
            $data['first_text'],
            $data['second_text'],
            $dateTime
        );
    }
}
