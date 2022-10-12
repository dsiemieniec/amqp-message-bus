<?php

declare(strict_types=1);

namespace App\Config\Arguments\Queue\Enum;

enum OverflowBehaviourType: string
{
    case DROP_HEAD = 'drop-head';
    case REJECT_PUBLISH = 'reject-publish';
    case REJECT_PUBLISH_DLX = 'reject-publish-dlx';
}
