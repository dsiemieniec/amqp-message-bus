<?php

namespace App\Config;

interface CommandPublisherConfig
{
    public function getCommandClass(): string;
    public function getPublisherTarget(): PublisherTarget;
    public function getConnection(): Connection;
}
