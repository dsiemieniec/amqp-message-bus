<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Config;

interface MessagePublisherConfig
{
    public function getPublisherTarget(): PublisherTarget;

    public function getConnection(): Connection;
}
