<?php

namespace App\Config;

use App\Exception\MissingMapItemException;

class CommandPublisherConfigsMap
{
    /** @var array<string, CommandPublisherConfig> */
    private array $configsByName;
    /** @var array<string, CommandPublisherConfig> */
    private array $configsByClass;

    public function __construct()
    {
        $this->configsByName = [];
        $this->configsByClass = [];
    }

    public function put(string $name, CommandPublisherConfig $config): void
    {
        $this->configsByName[$name] = $config;
        $this->configsByClass[$config->getCommandClass()] = $config;
    }

    public function getByName(string $name): CommandPublisherConfig
    {
        if (!\array_key_exists($name, $this->configsByName)) {
            throw new MissingMapItemException('Missing command config with name ' . $name);
        }

        return $this->configsByName[$name];
    }

    public function getByClass(string $commandClass): CommandPublisherConfig
    {
        if (!$this->existsByClass($commandClass)) {
            throw new MissingMapItemException('Missing command config for ' . $commandClass);
        }

        return $this->configsByClass[$commandClass];
    }

    public function existsByClass(string $commandClass): bool
    {
        return \array_key_exists($commandClass, $this->configsByClass);
    }
}
