<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Tests\Serializer;

use App\Command\SimpleCommand;
use PHPUnit\Framework\TestCase;
use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use Siemieniec\AsyncCommandBus\Serializer\DefaultCommandSerializer;

final class CommandSerializerTest extends TestCase
{
    public function testCanSerializeSimpleCommand(): void
    {
        $serializer = new DefaultCommandSerializer;
        $command = new SimpleCommand(1, 'test');

        self::assertEquals(
            $command,
            $serializer->deserialize(
                $serializer->serialize($command, new CommandProperties),
            ),
        );
    }

}
