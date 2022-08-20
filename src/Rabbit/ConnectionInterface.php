<?php

namespace App\Rabbit;

use PhpAmqpLib\Message\AMQPMessage;

interface ConnectionInterface
{
    public function declareQueue(Queue $queue): void;
    public function publish(AMQPMessage $msg, string $routingKey, string $exchange = ''): void;
    public function consume(ConsumerParameters $parameters, ConsumerCallbackInterface $callback): void;
    public function ack(AMQPMessage $msg): void;
    public function nack(AMQPMessage $msg): void;
}
