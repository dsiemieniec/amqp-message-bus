<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Config\Exchange;
use App\Config\PublisherTarget;
use App\Config\Queue;
use App\Config\QueueBinding;
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
