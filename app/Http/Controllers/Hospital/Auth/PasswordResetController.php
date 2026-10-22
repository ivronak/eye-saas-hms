<?php

namespace App\Http\Controllers\Hospital\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\HospitalPasswordOtp;
use App\Support\EmailRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /** Forgot password from the common /login page (hospital not known yet). */
    public function showGlobalForm(): View
    {
        return view('hospital.auth.forgot-password', [
            'slug' => null,
            'hospitalName' => 'Eyenosis',
            'formAction' => route('password.email'),
            'backUrl' => route('login'),
        ]);
    }

    public function sendGlobal(Request $request, HospitalPasswordOtp $otp): RedirectResponse
    {
        $request->validate([
            'email' => EmailRules::required(),
        ], EmailRules::messages('email'));

        $email = trim((string) $request->input('email'));
        // Plain queries: no hospital is selected yet, so skip the tenant-aware models.
        $tenantId = DB::table('hospital_users')
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->value('tenant_id');
        $tenant = $tenantId ? DB::table('tenants')->where('id', $tenantId)->first(['id', 'slug']) : null;

        if (! $tenant) {
            return back()->withErrors(['email' => 'No active account found with this email.'])->withInput();
        }

        $request->session()->put('pwd_otp_email', $email);
        $request->session()->put('pwd_otp_code', $otp->issue($tenant->id, $email));

        return redirect()->route('hospital.password.otp', ['slug' => $tenant->slug]);
    }

    /** Step 1: email -> OTP screen (OTP is shown there, not emailed). */
    public function sendResetLink(Request $request, string $slug, HospitalPasswordOtp $otp): RedirectResponse
    {
        $request->validate([
            'email' => EmailRules::required(),
        ], EmailRules::messages('email'));

        $tenant = app('tenant');
        $email = trim((string) $request->input('email'));

        if (! $otp->findUser($tenant->id, $email)) {
            return back()->withErrors(['email' => 'No active account found with this email.'])->withInput();
        }

        $request->session()->put('pwd_otp_email', $email);
        $request->session()->put('pwd_otp_code', $otp->issue($tenant->id, $email));

        return redirect()->route('hospital.password.otp', ['slug' => $slug]);
    }

    public function showOtpForm(Request $request, string $slug, HospitalPasswordOtp $otp): View|RedirectResponse
    {
        $email = $request->session()->get('pwd_otp_email');
        if (! $email) {
            return redirect()->route('hospital.password.request', ['slug' => $slug]);
        }

        $tenant = app('tenant');

        return view('hospital.auth.forgot-password-otp', [
            'slug' => $slug,
            'hospitalName' => $tenant?->name ?? 'Hospital',
            'email' => $email,
            'otp' => $request->session()->get('pwd_otp_code'),
            'wait' => $otp->waitSeconds($tenant->id, $email),
        ]);
    }

    public function resendOtp(Request $request, string $slug, HospitalPasswordOtp $otp): RedirectResponse
    {
        $email = $request->session()->get('pwd_otp_email');
        $tenant = app('tenant');

        if (! $email || ! $otp->findUser($tenant->id, $email)) {
            return redirect()->route('hospital.password.request', ['slug' => $slug]);
        }

        if ($otp->waitSeconds($tenant->id, $email) > 0) {
            return back()->withErrors(['otp' => 'Please wait before requesting a new OTP.']);
        }

        $request->session()->put('pwd_otp_code', $otp->issue($tenant->id, $email));

        return back()->with('success', 'A new OTP has been generated.');
    }

    public function verifyOtp(Request $request, string $slug, HospitalPasswordOtp $otp): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $email = $request->session()->get('pwd_otp_email');
        $tenant = app('tenant');
        $user = $email ? $otp->findUser($tenant->id, $email) : null;

        if (! $user) {
            return redirect()->route('hospital.password.request', ['slug' => $slug]);
        }

        $token = $otp->verify($user, (string) $request->input('otp'));
        if (! $token) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $request->session()->forget(['pwd_otp_email', 'pwd_otp_code']);

        return redirect()->route('hospital.password.reset', ['slug' => $slug, 'token' => $token, 'email' => $email]);
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
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('hospital.login', ['slug' => $slug])
                ->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->withInput();
    }
}
