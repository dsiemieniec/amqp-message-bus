<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsMessageHandler
{
}
