<?php

declare(strict_types=1);

namespace App\Command\Properties;

enum PropertyKey: string
{
    case ContentType = 'content_type';
    case ContentEncoding = 'content_encoding';
    case Headers = 'application_headers';
    case DeliveryMode = 'delivery_mode';
    case Priority = 'priority';
    case CorrelationId = 'correlation_id';
    case ReplyTo = 'reply_to';
    case Expiration = 'expiration';
    case MessageId = 'message_id';
    case Timestamp = 'timestamp';
    case Type = 'type';
    case UserId = 'user_id';
    case AppId = 'app_id';
    case ClusterId = 'cluster_id';

    public function equals(PropertyKey $key): bool
    {
        return $this->name === $key->name && $this->value === $key->value;
    }
}
