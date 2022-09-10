<?php

namespace App\Rabbit;

use App\Config\Binding;
use App\Config\Exchange;
use App\Config\Queue;
use PhpAmqpLib\Message\AMQPMessage;

interface ConnectionInterface
{
    public function declareQueue(Queue $queue): void;
    public function declareExchange(Exchange $exchange): void;
    public function bindQueue(Binding $binding): void;
    public function publish(AMQPMessage $msg, string $routingKey, string $exchange = ''): void;
    public function consume(ConsumerParameters $parameters, ConsumerCallbackInterface $callback): void;
    public function ack(AMQPMessage $msg): void;
    public function nack(AMQPMessage $msg): void;
}
