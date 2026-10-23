<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify OTP &mdash; {{ $hospitalName }}</title>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            margin: 0;
            font-family: var(--hms-font, 'Inter', sans-serif);
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #060E1A 0%, #0D2137 60%, #1B4F72 100%);
            padding: 1.5rem;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .35);
            overflow: hidden;
            border-top: 4px solid #1B4F72;
        }

        .login-body {
            padding: 2.25rem 2rem 2rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1B4F72, #2980B9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .875rem;
            box-shadow: 0 8px 24px rgba(27, 79, 114, .3);
        }

        .login-icon i {
            color: #fff;
            font-size: 1.625rem;
        }

        .login-header h1 {
            font-size: 1.375rem;
            font-weight: 800;
            color: #0D2137;
            margin: 0 0 .25rem;
            letter-spacing: -.02em;
        }

        .login-header p {
            font-size: .875rem;
            color: #64748B;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.125rem;
        }

        .form-label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .375rem;
        }

        .form-input {
            width: 100%;
            padding: .65rem .875rem;
            font-size: .875rem;
            color: #111827;
            border: 1.5px solid #D1D5DB;
            border-radius: 8px;
            background: #fff;
            outline: none;
            box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-input:focus {
            border-color: #2980B9;
            box-shadow: 0 0 0 3px rgba(41, 128, 185, .12);
        }

        .form-input.is-error {
            border-color: #C0392B;
        }

        .form-error {
            font-size: .75rem;
            color: #C0392B;
            margin-top: .3rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .btn-login {
            width: 100%;
            padding: .8rem;
            border: none;
            border-radius: 8px;
            background: #1B4F72;
            color: #fff;
            font-size: .9375rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: background .15s, transform .1s;
            box-shadow: 0 4px 12px rgba(27, 79, 114, .25);
        }

        .btn-login:hover {
            background: #154360;
        }

        .btn-login:active {
            transform: scale(.99);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .8125rem;
            color: #9CA3AF;
        }

        .login-footer a {
            color: #2980B9;
            text-decoration: none;
        }

        /* .login-footer a:hover { text-decoration: underline; } */
        .alert {
            padding: .75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: .875rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .alert-danger {
            background: #FADBD8;
            color: #641E16;
            border-left: 3px solid #C0392B;
        }

        .alert-success {
            background: #D5F5E3;
            color: #1A5C3A;
            border-left: 3px solid #27AE60;
        }

        /* Step indicator */
        .steps { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-bottom: 1.5rem; }
        .step { display: flex; align-items: center; gap: .4rem; font-size: .75rem; font-weight: 600; color: #9CA3AF; }
        .step-dot { width: 22px; height: 22px; border-radius: 50%; background: #E5E7EB; color: #6B7280;
            display: inline-flex; align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; }
        .step.active { color: #1B4F72; }
        .step.active .step-dot { background: #1B4F72; color: #fff; }
        .step.done .step-dot { background: #27AE60; color: #fff; }
        .step-line { width: 22px; height: 2px; background: #E5E7EB; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap > i { position: absolute; left: .875rem; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: .85rem; }
        .input-icon-wrap .form-input { padding-left: 2.4rem; }
        .btn-login:disabled { opacity: .7; cursor: not-allowed; }
        .otp-boxes { display: flex; gap: .5rem; justify-content: space-between; }
        .otp-box { width: 100%; max-width: 52px; height: 56px; text-align: center; font-size: 1.4rem; font-weight: 700;
            color: #0D2137; border: 1.5px solid #D1D5DB; border-radius: 10px; outline: none; box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s; }
        .otp-box:focus { border-color: #2980B9; box-shadow: 0 0 0 3px rgba(41, 128, 185, .15); }
        .otp-boxes.is-error .otp-box { border-color: #C0392B; }
        .otp-hint { background: #EAF4FB; border: 1px dashed #2980B9; border-radius: 10px; padding: .75rem 1rem;
            margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
        .otp-hint small { display: block; font-size: .7rem; color: #64748B; text-transform: uppercase; letter-spacing: .05em; }
        .otp-hint strong { font-size: 1.35rem; letter-spacing: .3em; color: #1B4F72; }
        .otp-fill { border: none; background: #1B4F72; color: #fff; font-size: .75rem; font-weight: 600;
            padding: .45rem .75rem; border-radius: 6px; cursor: pointer; }
        .resend-row { text-align: center; margin-top: 1.1rem; font-size: .8125rem; color: #6B7280; }
        .resend-btn { background: none; border: none; padding: 0; font: inherit; font-weight: 700; color: #2980B9; cursor: pointer; }
        .resend-btn:disabled { color: #9CA3AF; cursor: not-allowed; }
        .hint-text { font-size: .75rem; color: #9CA3AF; margin-top: .4rem; }
    </style>
</head>

<body>
    <div class="login-page">
        <div class="login-wrap">

            <div style="text-align:center;margin-bottom:1.25rem">
                <a href="{{ route('home') }}"
                    style="display:inline-flex;align-items:center;gap:.5rem;text-decoration:none;color:#fff;font-size:1rem;font-weight:700">
                    <i class="fa-solid fa-eye" style="font-size:1.25rem;color:#1ABC9C"></i>
                    <span>Eye<span style="color:#1ABC9C">HMS</span></span>
                </a>
            </div>

            <div class="login-card">
                <div class="login-body">
                    <div class="steps">
                        <div class="step done"><span class="step-dot"><i class="fa-solid fa-check" style="font-size:.6rem"></i></span> Email</div>
                        <span class="step-line"></span>
                        <div class="step active"><span class="step-dot">2</span> Verify</div>
                        <span class="step-line"></span>
                        <div class="step "><span class="step-dot">3</span> Password</div>
                    </div>

                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h1>Verify OTP</h1>
                        <p>We generated a code for <strong>{{ $email }}</strong></p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check" style="flex-shrink:0;margin-top:.1rem"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation" style="flex-shrink:0;margin-top:.1rem"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    @if($otp)
                        <div class="otp-hint">
                            <div>
                                <small>Your OTP</small>
                                <strong>{{ $otp }}</strong>
                            </div>
                            <button type="button" class="otp-fill" id="otpFill" data-otp="{{ $otp }}">Auto-fill</button>
                        </div>
                    @endif

                    <form method="POST" id="otpForm" action="{{ route('hospital.password.otp.verify', ['slug' => $slug]) }}">
                        @csrf
                        <input type="hidden" name="otp" id="otp">
                        <div class="form-group">
                            <label class="form-label">Enter 6-digit OTP</label>
                            <div class="otp-boxes @error('otp') is-error @enderror" id="otpBoxes">
                                @for($i = 0; $i < 6; $i++)
                                    <input type="text" class="otp-box" inputmode="numeric" maxlength="1"
                                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        aria-label="Digit {{ $i + 1 }}" {{ $i === 0 ? 'autofocus' : '' }}>
                                @endfor
                            </div>
                        </div>
                        <button type="submit" class="btn-login" id="verifyBtn">
                            <i class="fa-solid fa-check"></i> Verify OTP
                        </button>
                    </form>

                    <form method="POST" action="{{ route('hospital.password.otp.resend', ['slug' => $slug]) }}"
                        class="resend-row">
                        @csrf
                        Didn't get it?
                        <button type="submit" class="resend-btn" id="resendBtn" disabled>
                            <span id="resendLabel">Resend OTP</span>
                        </button>
                    </form>
                    <script>
                        (function () {
                            var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
                            var hidden = document.getElementById('otp');
                            var form = document.getElementById('otpForm');
                            function sync() {
                                hidden.value = boxes.map(function (b) { return b.value; }).join('');
                                return hidden.value.length === 6;
                            }
                            function fill(str) {
                                str = (str || '').replace(/\D/g, '').slice(0, 6);
                                boxes.forEach(function (b, i) { b.value = str[i] || ''; });
                                (boxes[Math.min(str.length, 5)]).focus();
                                if (sync()) submit();
                            }
                            function submit() {
                                var btn = document.getElementById('verifyBtn');
                                btn.disabled = true;
                                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';
                                form.submit();
                            }
                            boxes.forEach(function (b, i) {
                                b.addEventListener('input', function () {
                                    b.value = b.value.replace(/\D/g, '').slice(-1);
                                    if (b.value && i < 5) boxes[i + 1].focus();
                                    if (sync()) submit();
                                });
                                b.addEventListener('keydown', function (e) {
                                    if (e.key === 'Backspace' && !b.value && i > 0) boxes[i - 1].focus();
                                });
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
                            function tick() {
                                if (left > 0) {
                                    label.textContent = 'Resend in ' + left + 's';
                                    left--;
                                    setTimeout(tick, 1000);
                                } else {
                                    label.textContent = 'Resend OTP';
                                    btn.disabled = false;
                                }
                            }
                            tick();
                        })();
                    </script>

                    <div class="login-footer">
                        <a href="{{ route('hospital.password.request', ['slug' => $slug]) }}">
                            <i class="fa-solid fa-arrow-left"></i> Change email
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>