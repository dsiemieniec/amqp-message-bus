<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingCommandConfigException;

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
        if (!$this->has($commandClass)) {
            throw new MissingCommandConfigException(
                \sprintf('Config has not been defined for command %s', $commandClass)
            );
        }

        return $this->commands[$commandClass];
    }

    public function has(string $commandClass): bool
    {
        return \array_key_exists($commandClass, $this->commands);
    }
}
