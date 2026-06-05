<?php

namespace Aghfatehi\Msegat\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MessageSending
{
    use Dispatchable;

    public function __construct(
        public array $data,
    ) {
    }
}
