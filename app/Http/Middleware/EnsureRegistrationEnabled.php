<?php

namespace App\Http\Middleware;

use App\Models\AdminSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(AdminSetting::bool('registration_enabled', true), 403, 'Registration is currently disabled by admin.');

        return $next($request);
    }
}
