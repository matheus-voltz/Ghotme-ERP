<?php

namespace App\Livewire\Profile;

use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm as JetstreamLogoutOtherBrowserSessionsForm;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

class LogoutOtherBrowserSessionsForm extends JetstreamLogoutOtherBrowserSessionsForm
{
    /**
     * Log out from other browser sessions and revoke other API tokens.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function logoutOtherBrowserSessions(StatefulGuard $guard)
    {
        // Call the parent method to handle standard sessions
        parent::logoutOtherBrowserSessions($guard);

        // Also revoke all other Sanctum tokens (mobile sessions)
        // We delete all tokens EXCEPT the current one if it were an API request,
        // but since this is a web request, we revoke ALL existing tokens to be safe
        // (the mobile app will need to log in again).
        Auth::user()->tokens()->delete();

        $this->dispatch('loggedOut');
    }
}
