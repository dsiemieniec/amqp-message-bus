<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Command\Properties;

interface HeaderInterface
{
    public function getName(): string;

    public function getValue(): string;
}
