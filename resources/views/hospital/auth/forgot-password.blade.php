<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password &mdash; {{ $hospitalName }}</title>
    @include('hospital.auth._recovery-styles')
</head>

<body>
    @include('hospital.auth._recovery-open', ['step' => 1])
                <div class="rec-top">
                    <a href="{{ $backUrl ?? route('hospital.login', ['slug' => $slug]) }}" class="rec-back" aria-label="Back to login"><i class="fa-solid fa-arrow-left"></i></a>
                    <span class="rec-step">Step 1 of 3</span>
                </div>
                <div class="rec-icon"><i class="fa-solid fa-lock"></i></div>
                <h1 class="rec-title">Forgot password?</h1>
                <p class="rec-sub">No worries. Enter the email linked to your account and we'll get you a one-time code.</p>

                @include('hospital.auth._recovery-alerts')

                <form method="POST" id="forgotForm" action="{{ $formAction ?? route('hospital.password.email', ['slug' => $slug]) }}">
                    @csrf
                    <div class="rec-field">
                        <label class="rec-label" for="email">Email address</label>
                        <i class="fa-solid fa-envelope lead"></i>
                        <input type="email" id="email" name="email" class="rec-input @error('email') is-error @enderror"
                            value="{{ old('email') }}" placeholder="you@hospital.com" required autofocus autocomplete="email">
                    </div>
                    <button type="submit" class="rec-btn" id="forgotBtn">Continue <i class="fa-solid fa-arrow-right"></i></button>
                </form>
                <div class="rec-foot"><a href="{{ $backUrl ?? route('hospital.login', ['slug' => $slug]) }}">Remembered it? Back to login</a></div>
    @include('hospital.auth._recovery-close')

    <script>
        document.getElementById('forgotForm').addEventListener('submit', function () {
            var b = document.getElementById('forgotBtn');
            b.disabled = true;
            b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
        });
    </script>
</body>

</html>
