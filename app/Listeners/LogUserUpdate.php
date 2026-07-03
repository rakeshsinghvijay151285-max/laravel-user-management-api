<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserUpdated $event): void
    {
        Log::info('User updated', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'updated_at' => now(),
        ]);
    }
}
