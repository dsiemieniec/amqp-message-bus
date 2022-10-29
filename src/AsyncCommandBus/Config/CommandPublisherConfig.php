<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Config;

use Siemieniec\AsyncCommandBus\Config\Connection;
use Siemieniec\AsyncCommandBus\Config\PublisherTarget;

interface CommandPublisherConfig
{
    public function getPublisherTarget(): PublisherTarget;

    public function getConnection(): Connection;
}
