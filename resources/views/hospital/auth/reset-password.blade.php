<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password &mdash; {{ $hospitalName }}</title>
    @include('hospital.auth._recovery-styles')
</head>

<body>
    @include('hospital.auth._recovery-open', ['step' => 3])
                <div class="rec-top">
                    <a href="{{ route('hospital.login', ['slug' => $slug]) }}" class="rec-back" aria-label="Back to login"><i class="fa-solid fa-arrow-left"></i></a>
                    <span class="rec-step">Step 3 of 3</span>
                </div>
                <div class="rec-icon"><i class="fa-solid fa-key"></i></div>
                <h1 class="rec-title">Set a new password</h1>
                <p class="rec-sub">Choose a strong password you haven't used before. Use at least 8 characters.</p>

                @include('hospital.auth._recovery-alerts')

                <form method="POST" id="resetForm" action="{{ route('hospital.password.update', ['slug' => $slug]) }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">

                    <div class="rec-field">
                        <label class="rec-label" for="password">New password</label>
                        <i class="fa-solid fa-lock lead"></i>
                        <input type="password" id="password" name="password" class="rec-input @error('password') is-error @enderror"
                            placeholder="Min. 8 characters" required autofocus autocomplete="new-password">
                    </div>
                    <div class="rec-field">
                        <label class="rec-label" for="password_confirmation">Confirm password</label>
                        <i class="fa-solid fa-lock lead"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="rec-input"
                            placeholder="Re-enter password" required autocomplete="new-password">
                        <div class="rec-hint" id="matchHint"></div>
                    </div>
                    <button type="submit" class="rec-btn"><i class="fa-solid fa-check"></i> Reset password</button>
                </form>
                <div class="rec-foot"><a href="{{ route('hospital.login', ['slug' => $slug]) }}">Back to login</a></div>
    @include('hospital.auth._recovery-close')

    <script>
        (function () {
            var p = document.getElementById('password'), c = document.getElementById('password_confirmation'),
                h = document.getElementById('matchHint');
            function check() {
                if (!c.value) { h.textContent = ''; return; }
                var ok = p.value === c.value;
                h.textContent = ok ? 'Passwords match' : 'Passwords do not match yet';
                h.style.color = ok ? '#1E6B42' : '#C0392B';
            }
            p.addEventListener('input', check);
            c.addEventListener('input', check);
        })();
    </script>
</body>

</html>
