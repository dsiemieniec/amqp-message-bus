<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Command\AnotherSimpleCommand;
use Siemieniec\AsyncCommandBus\Command\CommandInterface;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Exception\DeserializationException;
use Siemieniec\AsyncCommandBus\Exception\SerializationException;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelope;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelopeInterface;
use DateTimeImmutable;
use Siemieniec\AsyncCommandBus\Serializer\CommandSerializerInterface;

class SampleCommandSerializer implements CommandSerializerInterface
{
    /**
     * @param AnotherSimpleCommand $command
     */
    public function serialize(CommandInterface $command, CommandProperties $properties): MessageEnvelopeInterface
    {
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

    public function deserialize(MessageEnvelopeInterface $envelope): CommandInterface
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
