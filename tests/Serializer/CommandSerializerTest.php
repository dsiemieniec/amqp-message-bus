<?php

namespace App\Tests\Serializer;

use App\Command\Properties\CommandProperties;
use App\Command\SimpleCommand;
use App\Serializer\DefaultCommandSerializer;
use PHPUnit\Framework\TestCase;

class CommandSerializerTest extends TestCase
{
    public function testCanSerializeSimpleCommand(): void
    {
        $serializer = new DefaultCommandSerializer();
        $command = new SimpleCommand(1, 'test');

        self::assertEquals(
            $command,
            $serializer->deserialize(
                $serializer->serialize($command, new CommandProperties())
            )
        );
    }
}
