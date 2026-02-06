<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (isset($event->user) && $event->user instanceof \App\Models\User) {
            $event->user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
        }
    }
}
