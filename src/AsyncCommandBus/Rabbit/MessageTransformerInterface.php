<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;
use Siemieniec\AsyncCommandBus\Rabbit\MessageEnvelopeInterface;

interface MessageTransformerInterface
{
    public function transformMessage(AMQPMessage $message): MessageEnvelopeInterface;

    public function transformEnvelope(MessageEnvelopeInterface $envelope): AMQPMessage;
}
