<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\Platform\MailConfigService;
use App\Support\EmailRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Forgot password for the unified /login page.
 * Looks up the hospital user by email, then sends the reset mail
 * through the same Super Admin SMTP used for welcome / test emails.
 */
class UnifiedPasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('hospital.auth.forgot-password', [
            'hospitalName' => config('app.name', 'EYENOSIS'),
            'formAction' => route('password.email'),
            'loginUrl' => route('login'),
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
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
        } catch (\Throwable $exception) {
            return back()
                ->withErrors(['email' => 'Could not send reset email. Please try again or contact support.'])
                ->withInput();
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput();
    }

    public function showResetForm(string $token): View
    {
        return view('hospital.auth.reset-password', [
            'token' => $token,
            'email' => request('email', ''),
            'hospitalName' => config('app.name', 'EYENOSIS'),
            'formAction' => route('password.update'),
            'loginUrl' => route('login'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
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
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Sign every mobile/tablet device out; they must use the new password.
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput();
    }
}
