<?php

namespace App\Utils;

use InvalidArgumentException;

class Delay
{
    private function __construct(
        private int $value
    ) {
    }

    public static function miliseconds(int $value): Delay
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Value must be greater or equal to 0');
        }

        return new Delay($value);
    }

    public static function seconds(int $value): Delay
    {
        return self::miliseconds($value * 1000);
    }

    public static function minutes(int $value): Delay
    {
        return self::seconds($value * 60);
    }

    public static function hours(int $value): Delay
    {
        return self::minutes($value * 60);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
