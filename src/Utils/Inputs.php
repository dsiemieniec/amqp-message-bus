<?php

namespace App\Utils;

class Inputs
{
    public static function boolValue(mixed $value): bool
    {
        if ($value === 'false') {
            return false;
        }

        return true === (bool)$value || $value === 'true' || 1 === (int)$value;
    }
}
