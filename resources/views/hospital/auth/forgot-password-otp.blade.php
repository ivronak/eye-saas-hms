<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify OTP &mdash; {{ $hospitalName }}</title>
    @include('hospital.auth._recovery-styles')
</head>

<body>
    @include('hospital.auth._recovery-open', ['step' => 2])
                <div class="rec-top">
                    <a href="{{ route('hospital.password.request', ['slug' => $slug]) }}" class="rec-back" aria-label="Change email"><i class="fa-solid fa-arrow-left"></i></a>
                    <span class="rec-step">Step 2 of 3</span>
                </div>
                <div class="rec-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h1 class="rec-title">Enter your code</h1>
                <p class="rec-sub">Type the 6-digit code for <strong>{{ $email }}</strong>. It stays valid for 10 minutes.</p>

                @include('hospital.auth._recovery-alerts')

                @if($otp)
                    <div class="otp-code">
                        <div><small>Your code</small><strong>{{ $otp }}</strong></div>
                        <button type="button" class="otp-chip" id="otpFill" data-otp="{{ $otp }}"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-fill</button>
                    </div>
                @endif

                <form method="POST" id="otpForm" action="{{ route('hospital.password.otp.verify', ['slug' => $slug]) }}">
                    @csrf
                    <input type="hidden" name="otp" id="otp">
                    <div class="otp-boxes @error('otp') is-error @enderror" id="otpBoxes">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text" class="otp-box" inputmode="numeric" maxlength="1"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="Digit {{ $i + 1 }}" {{ $i === 0 ? 'autofocus' : '' }}>
                        @endfor
                    </div>
                    <button type="submit" class="rec-btn" id="verifyBtn">Verify code</button>
                </form>

                <form method="POST" action="{{ route('hospital.password.otp.resend', ['slug' => $slug]) }}" class="rec-resend">
                    @csrf
                    Didn't get a code?
                    <button type="submit" class="rec-link-btn" id="resendBtn" disabled><span id="resendLabel">Resend code</span></button>
                </form>
                <div class="rec-foot"><a href="{{ route('hospital.password.request', ['slug' => $slug]) }}">Use a different email</a></div>
    @include('hospital.auth._recovery-close')

    <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
            var hidden = document.getElementById('otp');
            var form = document.getElementById('otpForm');
            var sent = false;
            function sync() {
                boxes.forEach(function (b) { b.classList.toggle('filled', b.value !== ''); });
                hidden.value = boxes.map(function (b) { return b.value; }).join('');
                return hidden.value.length === 6;
            }
            function submit() {
                if (sent) return;
                sent = true;
                var btn = document.getElementById('verifyBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';
                form.submit();
            }
            function fill(str) {
                str = (str || '').replace(/\D/g, '').slice(0, 6);
                boxes.forEach(function (b, i) { b.value = str[i] || ''; });
                boxes[Math.min(str.length, 5)].focus();
                if (sync()) submit();
            }
            boxes.forEach(function (b, i) {
                b.addEventListener('input', function () {
                    b.value = b.value.replace(/\D/g, '').slice(-1);
                    if (b.value && i < 5) boxes[i + 1].focus();
                    if (sync()) submit();
                });
                b.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !b.value && i > 0) { boxes[i - 1].value = ''; boxes[i - 1].focus(); sync(); }
                });
                b.addEventListener('focus', function () { b.select(); });
                b.addEventListener('paste', function (e) {
                    e.preventDefault();
                    fill((e.clipboardData || window.clipboardData).getData('text'));
                });
            });
            form.addEventListener('submit', function (e) {
                if (!sync()) { e.preventDefault(); boxes[0].focus(); }
            });
            var fillBtn = document.getElementById('otpFill');
            if (fillBtn) fillBtn.addEventListener('click', function () { fill(fillBtn.dataset.otp); });

            var left = {{ (int) $wait }};
            var btn = document.getElementById('resendBtn');
            var label = document.getElementById('resendLabel');
            (function tick() {
                if (left > 0) {
                    label.textContent = 'Resend in ' + left + 's';
                    left--;
                    setTimeout(tick, 1000);
                } else {
                    label.textContent = 'Resend code';
                    btn.disabled = false;
                }
            })();
        })();
    </script>
</body>

</html>
