<?php

namespace App\Http\Controllers\Hospital\Auth;

use App\Http\Controllers\Controller;
use App\Services\Platform\MailConfigService;
use App\Support\EmailRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Hospital Forgot / Reset Password Controller
 *
 * Uses Laravel's Password Broker with 'hospital_users' broker.
 * URL: hmssaas.com/{slug}/forgot-password, reset-password
 */
class PasswordResetController extends Controller
{
    public function showForgotForm(string $slug): View
    {
        $tenant = app('tenant');

        return view('hospital.auth.forgot-password', [
            'slug' => $slug,
            'hospitalName' => $tenant?->name ?? 'Hospital',
        ]);
    }

    public function sendResetLink(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'email' => EmailRules::required(),
        ], EmailRules::messages('email'));

        if (! MailConfigService::apply()) {
            return back()
                ->withErrors(['email' => 'Email is not configured. Please contact support.'])
                ->withInput();
        }

        try {
            $status = Password::broker('hospital_users')->sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable) {
            return back()
                ->withErrors(['email' => 'Could not send reset email. Please try again or contact support.'])
                ->withInput();
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput();
    }

    public function showResetForm(string $slug, string $token): View
    {
        $tenant = app('tenant');

        return view('hospital.auth.reset-password', [
            'slug' => $slug,
            'token' => $token,
            'email' => request('email', ''),
            'hospitalName' => $tenant?->name ?? 'Hospital',
        ]);
    }

    public function resetPassword(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => EmailRules::required(),
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], EmailRules::messages('email'));

        $status = Password::broker('hospital_users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Sign every mobile/tablet device out; they must use the new password.
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('hospital.login', ['slug' => $slug])
                ->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput();
    }
}
