<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Rabbit;

use Siemieniec\AmqpMessageBus\Config\Exchange;
use Siemieniec\AmqpMessageBus\Config\PublisherTarget;
use Siemieniec\AmqpMessageBus\Config\Queue;
use Siemieniec\AmqpMessageBus\Config\QueueBinding;
use PhpAmqpLib\Message\AMQPMessage;

interface ConnectionInterface
{
    public function declareQueue(Queue $queue): void;

    public function declareExchange(Exchange $exchange): void;

    public function bindQueue(Exchange $exchange, QueueBinding $queueBinding): void;

    public function publish(AMQPMessage $msg, PublisherTarget $target): void;

    public function consume(Queue $queue, ConsumerCallbackInterface $callback): void;

    public function ack(AMQPMessage $msg): void;

    public function nack(AMQPMessage $msg, bool $requeue): void;
}
