<?php

namespace App\Context\User\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDataImported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
