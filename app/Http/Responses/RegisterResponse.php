<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Redirect the user after a successful registration.
     */
    public function toResponse($request): Response
    {
        return redirect()->route('verification.notice');
    }
}
