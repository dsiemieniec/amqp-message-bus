<?php

declare(strict_types=1);

namespace App\Config;

use App\Config\Arguments\Queue\BooleanQueueArgument;
use App\Config\Arguments\Queue\Enum\OverflowBehaviourType;
use App\Config\Arguments\Queue\Enum\QueueArgumentKey;
use App\Config\Arguments\Queue\Enum\QueueModeType;
use App\Config\Arguments\Queue\Enum\QueueVersionType;
use App\Config\Arguments\Queue\IntegerQueueArgument;
use App\Config\Arguments\Queue\OverflowBehaviourArgument;
use App\Config\Arguments\Queue\QueueArgumentInterface;
use App\Config\Arguments\Queue\QueueArgumentsCollection;
use App\Config\Arguments\Queue\QueueModeArgument;
use App\Config\Arguments\Queue\QueueVersionArgument;
use App\Config\Arguments\Queue\StringQueueArgument;
use App\Exception\InvalidQueueArgumentKeyException;
use App\Exception\InvalidQueueArgumentValueException;

class QueueArgumentsFactory
{
    /**
     * @param array<string, mixed> $arguments
     * @return QueueArgumentsCollection
     */
    public function createCollection(array $arguments): QueueArgumentsCollection
    {
        $collection = new QueueArgumentsCollection();
        foreach ($arguments as $key => $value) {
            $collection[] = $this->create($key, $value);
        }

        return $collection;
    }

    public function create(string $key, mixed $value): QueueArgumentInterface
    {
        $keyEnum = QueueArgumentKey::tryFrom($key);
        if ($keyEnum === null) {
            throw new InvalidQueueArgumentKeyException(
                \sprintf('Unsupported queue argument configured %s', $key)
            );
        }
        switch ($keyEnum) {
            case QueueArgumentKey::AUTO_EXPIRE:
            case QueueArgumentKey::MESSAGE_TTL:
            case QueueArgumentKey::MAX_LENGTH:
            case QueueArgumentKey::MAX_LENGTH_BYTES:
            case QueueArgumentKey::MAXIMUM_PRIORITY:
                return new IntegerQueueArgument($keyEnum, (int)$value);

            case QueueArgumentKey::DEAD_LETTER_EXCHANGE:
            case QueueArgumentKey::DEAD_LETTER_ROUTING_KEY:
            case QueueArgumentKey::MASTER_LOCATOR:
                return new StringQueueArgument($keyEnum, (string)$value);

            case QueueArgumentKey::SINGLE_ACTIVE_CONSUMER:
                return new BooleanQueueArgument($keyEnum, (bool)$value);

            case QueueArgumentKey::OVERFLOW_BEHAVIOUR:
                $overflowBehaviour = OverflowBehaviourType::tryFrom($value);
                if ($overflowBehaviour === null) {
                    throw new InvalidQueueArgumentValueException(
                        \sprintf('Invalid value for %s provided %s', $key, $value)
                    );
                }
                return new OverflowBehaviourArgument($overflowBehaviour);

            case QueueArgumentKey::MODE:
                $mode = QueueModeType::tryFrom($value);
                if ($mode === null) {
                    throw new InvalidQueueArgumentValueException(
                        \sprintf('Invalid value for %s provided %s', $key, $value)
                    );
                }
                return new QueueModeArgument($mode);

            case QueueArgumentKey::VERSION:
                $version = QueueVersionType::tryFrom($value);
                if ($version === null) {
                    throw new InvalidQueueArgumentValueException(
                        \sprintf('Invalid value for %s provided %s', $key, $value)
                    );
                }
                return new QueueVersionArgument($version);

            default:
                throw new InvalidQueueArgumentKeyException(
                    \sprintf('Unsupported queue argument configured %s', $key)
                );
        }
    }
}
