<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password &mdash; {{ $hospitalName }}</title>
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
                        <div class="step active"><span class="step-dot">1</span> Email</div>
                        <span class="step-line"></span>
                        <div class="step "><span class="step-dot">2</span> Verify</div>
                        <span class="step-line"></span>
                        <div class="step "><span class="step-dot">3</span> Password</div>
                    </div>

                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h1>Forgot Password</h1>
                        <p>Enter your email to get a one-time password (OTP)</p>
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

                    <form method="POST" id="forgotForm" action="{{ $formAction ?? route('hospital.password.email', ['slug' => $slug]) }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" id="email" name="email"
                                    class="form-input @error('email') is-error @enderror" value="{{ old('email') }}"
                                    placeholder="your@email.com" required autofocus autocomplete="email">
                            </div>
                            <div class="hint-text">Use the email your hospital account was registered with.</div>
                        </div>

                        <button type="submit" class="btn-login" id="forgotBtn">
                            <i class="fa-solid fa-paper-plane"></i> Continue
                        </button>
                    </form>

                    <div class="login-footer">
                        <a href="{{ $backUrl ?? route('hospital.login', ['slug' => $slug]) }}">
                            <i class="fa-solid fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
<script>
        document.getElementById('forgotForm').addEventListener('submit', function () {
            var b = document.getElementById('forgotBtn');
            b.disabled = true;
            b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
        });
    </script>
</body>

</html>