<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\PropertyAccess\Exception\RuntimeException;

class TimeLimitException extends RuntimeException
{
    public function __construct(int $limit)
    {
        parent::__construct(\sprintf('Time limit of %d seconds has been reached', $limit));
    }
}
