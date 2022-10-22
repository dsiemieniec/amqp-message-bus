<?php

declare(strict_types = 1);

namespace Siemieniec\AsyncCommandBus\Command\Properties;

use ArrayAccess;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

use function get_debug_type;
use function is_string;
use function sprintf;

use Siemieniec\AsyncCommandBus\Command\Properties\InvalidArgumentException;

use function array_key_exists;
use function implode;
use function array_map;
use function is_integer;

/**
 * @method string contentType()
 * @method string contentEncoding()
 * @method int deliveryMode()
 * @method int priority()
 * @method string correlationId()
 * @method string replyTo()
 * @method int expiration()
 * @method string messageId()
 * @method int timestamp()
 * @method string userId()
 * @method string appId()
 * @method string clusterId()
 * @method array headers()
 */
final class CommandProperties implements ArrayAccess
{
    /** @var array<string, \Siemieniec\AsyncCommandBus\Command\Properties\CommandPropertyInterface> */
    private array $properties = [];
    private CamelCaseToSnakeCaseNameConverter $nameConverter;

    public function __construct(CommandPropertyInterface ...$properties)
    {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($properties as $property) {
            $this[] = $property;
        }
    }

    public static function builder(): CommandPropertiesBuilder
    {
        return new CommandPropertiesBuilder();
    }

    /**
     * @param array<int|string, mixed> $arguments
     * @return int|string|array<string, string>|null
     */
    public function __call(string $name, array $arguments): int|string|array|null
    {
        $name = $name !== 'headers'
            ? $this->nameConverter->normalize($name)
            : PropertyKey::Headers;

        return $this[$name]?->getValue();
    }

    /** @param \Siemieniec\AsyncCommandBus\Command\Properties\PropertyKey|string $offset */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($this->getPropertyKey($offset)->value, $this->properties);
    }

    /** @param \Siemieniec\AsyncCommandBus\Command\Properties\PropertyKey|string $offset */
    public function offsetGet(mixed $offset): ?CommandPropertyInterface
    {
        return $this->properties[$this->getPropertyKey($offset)->value] ?? null;
    }

    /**
     * @param \Siemieniec\AsyncCommandBus\Command\Properties\PropertyKey|string|null $offset
     * @param \Siemieniec\AsyncCommandBus\Command\Properties\CommandPropertyInterface|\Siemieniec\AsyncCommandBus\Command\Properties\HeaderInterface|\Siemieniec\AsyncCommandBus\Command\Properties\DeliveryMode|string|int $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            if (!($value instanceof CommandPropertyInterface) && !($value instanceof HeaderInterface)) {
                throw new InvalidArgumentException('Unknown property');
            }

            $key = $value instanceof HeaderInterface
                ? PropertyKey::Headers
                : $value->getKey();
        } else {
            $key = $this->getPropertyKey($offset);
        }

        if ($key->equals(PropertyKey::Headers)) {
            if (!isset($this->properties[PropertyKey::Headers->value])) {
                $this->properties[PropertyKey::Headers->value] = new Headers();
            }

            $this->properties[PropertyKey::Headers->value][] = $value;
        } else {
            $this->properties[$key->value] = $this->getProperty($key, $value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->properties[$this->getPropertyKey($offset)->value]);
    }

    /** @return array<string, int|string|array<string, string>> */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->properties as $key => $value) {
            $result[$key] = $value->getValue();
        }

        return $result;
    }

    private function getPropertyKey(mixed $offset): PropertyKey
    {
        if ($offset instanceof PropertyKey) {
            return $offset;
        }

        if (is_string($offset)) {
            $key = PropertyKey::tryFrom($offset);

            if ($key !== null) {
                return $key;
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid offset %s Allowed values %s',
                $offset,
                implode(
                    ', ',
                    array_map(
                        static fn (PropertyKey $propertyKey): string => $propertyKey->value,
                        PropertyKey::cases()
                    )
                )
            )
        );
    }

    private function getProperty(PropertyKey $key, mixed $value): CommandPropertyInterface
    {
        if ($value instanceof CommandPropertyInterface) {
            return $value;
        }

        switch ($key) {
            case PropertyKey::AppId:
            case PropertyKey::ClusterId:
            case PropertyKey::ContentEncoding:
            case PropertyKey::ContentType:
            case PropertyKey::CorrelationId:
            case PropertyKey::MessageId:
            case PropertyKey::ReplyTo:
            case PropertyKey::Type:
            case PropertyKey::UserId:
                $this->assertStringProperty($key, $value);

                return new StringProperty($key, $value);

            case PropertyKey::Expiration:
            case PropertyKey::Priority:
            case PropertyKey::Timestamp:
                $this->assertIntegerProperty($key, $value);

                return new IntegerProperty($key, $value);

            case PropertyKey::DeliveryMode:
                return new DeliveryModeProperty($value);

            default:
                throw new InvalidArgumentException('Unknown property');
        }
    }

    private function assertStringProperty(PropertyKey $key, mixed $value): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Property %s expects string but %s given',
                    $key->value,
                    get_debug_type($value)
                )
            );
        }
    }

    private function assertIntegerProperty(PropertyKey $key, mixed $value): void
    {
        if (!is_integer($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Property %s expects integer but %s given',
                    $key->value,
                    get_debug_type($value)
                )
            );
        }
    }
}
