<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect the user after a successful login.
     */
    public function toResponse($request): Response
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(config('fortify.home'));
        }

        return redirect()->route('verification.notice');
    }
}
