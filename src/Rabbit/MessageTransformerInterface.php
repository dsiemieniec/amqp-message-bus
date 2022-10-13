<?php

declare(strict_types=1);

namespace App\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;

interface MessageTransformerInterface
{
    public function transformMessage(AMQPMessage $message): MessageEnvelopeInterface;

    public function transformEnvelope(MessageEnvelopeInterface $envelope): AMQPMessage;
}
