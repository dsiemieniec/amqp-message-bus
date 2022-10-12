<?php

declare(strict_types=1);

namespace App\Config;

use App\Exception\MissingQueueException;
use Exception;

class QueuesMap
{
    /** @var array<string, Queue> */
    private array $queues;

    public function __construct()
    {
        $this->queues = [];
    }

    public function put(string $name, Queue $queue): void
    {
        $this->queues[$name] = $queue;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->queues);
    }

    /**
     * @throws Exception
     */
    public function get(string $name): Queue
    {
        if (!$this->has($name)) {
            throw new MissingQueueException(\sprintf('Queue %s has not been defined', $name));
        }

        return $this->queues[$name];
    }

    /** @return Queue[] */
    public function all(): array
    {
        return \array_values($this->queues);
    }
}
