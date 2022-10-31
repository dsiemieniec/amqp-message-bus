<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Message\Properties;

enum DeliveryMode: int
{
    case NonPersistent = 1;
    case Persistent = 2;
}
