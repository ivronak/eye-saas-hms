<?php

namespace App\Services\Auth;

use App\Models\Hospital\HospitalUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Forgot-password OTP for hospital users (web + mobile/tablet API).
 *
 * The OTP is NOT emailed yet — it is returned to the caller so the OTP
 * screen can display it. Once verified, a normal password-broker reset
 * token is issued so the existing reset step is reused.
 */
class HospitalPasswordOtp
{
    public const RESEND_SECONDS = 30;
    private const TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function findUser(int $tenantId, string $email): ?HospitalUser
    {
        return HospitalUser::query()
            ->where('tenant_id', $tenantId)
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();
    }

    /** Seconds the caller must still wait before another OTP may be issued. */
    public function waitSeconds(int $tenantId, string $email): int
    {
        $data = Cache::get($this->key($tenantId, $email));
        if (! $data) {
            return 0;
        }

        return max(0, self::RESEND_SECONDS - (time() - $data['sent_at']));
    }

    /** Generates and stores a fresh OTP; returns the plain code. */
    public function issue(int $tenantId, string $email): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->key($tenantId, $email), [
            'hash' => Hash::make($otp),
            'sent_at' => time(),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        return $otp;
    }

    /** Returns a password-reset token on success, null otherwise. */
    public function verify(HospitalUser $user, string $otp): ?string
    {
        $key = $this->key((int) $user->tenant_id, $user->email);
        $data = Cache::get($key);

        if (! $data || $data['attempts'] >= self::MAX_ATTEMPTS) {
            return null;
        }

        if (! Hash::check(trim($otp), $data['hash'])) {
            $data['attempts']++;
            Cache::put($key, $data, now()->addMinutes(self::TTL_MINUTES));

            return null;
        }

        Cache::forget($key);

        return Password::broker('hospital_users')->createToken($user);
    }

    private function key(int $tenantId, string $email): string
    {
        return 'hospital_pwd_otp:'.$tenantId.':'.strtolower(trim($email));
    }
}
