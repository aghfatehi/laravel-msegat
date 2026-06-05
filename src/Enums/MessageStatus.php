<?php

namespace Aghfatehi\Msegat\Enums;

/**
 * Enumeration of possible SMS message delivery statuses.
 */
enum MessageStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Queued = 'queued';
    case Cancelled = 'cancelled';
}
