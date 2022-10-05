<?php

declare(strict_types=1);

namespace App\Config;

interface CommandPublisherConfig
{
    public function getPublisherTarget(): PublisherTarget;
    public function getConnection(): Connection;
}
