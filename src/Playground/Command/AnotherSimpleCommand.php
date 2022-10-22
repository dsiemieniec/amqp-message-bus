<?php

declare(strict_types=1);

namespace App\Command;

use DateTimeInterface;
use Siemieniec\AsyncCommandBus\Command\CommandInterface;

class AnotherSimpleCommand implements CommandInterface
{
    public function __construct(
        private string $firstText,
        private string $secondText,
        private DateTimeInterface $dateTime
    ) {
    }

    public function getFirstText(): string
    {
        return $this->firstText;
    }

    public function getSecondText(): string
    {
        return $this->secondText;
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }
}
