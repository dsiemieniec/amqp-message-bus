<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingMapItemException;

class CommandConfigsMap
{
    /** @var array<string, CommandConfig> */
    private array $commands;

    public function __construct()
    {
        $this->commands = [];
    }

    public function put(CommandConfig $config): void
    {
        $this->commands[$config->getCommandClass()] = $config;
    }

    public function get(string $commandClass): CommandConfig
    {
        if (!$this->exists($commandClass)) {
            throw new MissingMapItemException('Missing command config for ' . $commandClass);
        }

        return $this->commands[$commandClass];
    }

    public function exists(string $commandClass): bool
    {
        return \array_key_exists($commandClass, $this->commands);
    }
}
