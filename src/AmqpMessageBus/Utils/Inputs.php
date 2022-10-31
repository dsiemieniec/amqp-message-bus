<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Utils;

class Inputs
{
    public static function boolValue(mixed $value): bool
    {
        if ($value === 'false') {
            return false;
        }

        return true === (bool)$value || $value === 'true' || 1 === (int)$value;
    }

    public static function floatValue(mixed $value): float
    {
        if (\is_string($value)) {
            $value = \str_replace(',', '.', $value);
        }

        return \floatval($value);
    }
}
