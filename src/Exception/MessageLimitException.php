<?php

namespace App\Exception;

use Symfony\Component\PropertyAccess\Exception\RuntimeException;

class MessageLimitException extends RuntimeException
{
    public function __construct(int $limit)
    {
        parent::__construct(\sprintf('Limit of %d messages has been reached', $limit));
    }
}
