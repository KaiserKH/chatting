<?php

namespace App\Http\Middleware;

use App\Models\AdminSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerificationIfEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminSetting::bool('email_verification_enabled', false)) {
            return $next($request);
        }

        if ($request->user()?->hasVerifiedEmail()) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
