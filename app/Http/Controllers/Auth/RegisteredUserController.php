<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        abort_unless(AdminSetting::bool('registration_enabled', true), 403, 'Registration is currently disabled.');

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(AdminSetting::bool('registration_enabled', true), 403, 'Registration is currently disabled.');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $baseUsername = (string) ($request->username ?: Str::slug((string) $request->name, separator: '_') ?: Str::before((string) $request->email, '@'));
        $username = Str::lower($baseUsername);
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = Str::lower($baseUsername).'_'.$suffix;
            $suffix++;
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'status_message' => 'Hey there! I am using Chatting.',
        ]);

        if (AdminSetting::bool('email_verification_enabled', false)) {
            event(new Registered($user));
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
