<div class="split-layout">
    <div class="auth-shell">
        <div class="auth-banner">
            <div class="banner-content">
                <img src="{{ platform_logo_light_url() }}" alt="Eye HMS" class="banner-logo platform-logo-on-light">
                <div class="hero-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h2>Recover your account</h2>
                <p>Three quick steps and you are back in your hospital workspace.</p>
                <ol class="banner-steps">
                    <li class="{{ $step > 1 ? 'done' : ($step == 1 ? 'active' : '') }}"><span>{!! $step > 1 ? '<i class="fa-solid fa-check"></i>' : '1' !!}</span> Enter your email</li>
                    <li class="{{ $step > 2 ? 'done' : ($step == 2 ? 'active' : '') }}"><span>{!! $step > 2 ? '<i class="fa-solid fa-check"></i>' : '2' !!}</span> Verify the one-time code</li>
                    <li class="{{ $step == 3 ? 'active' : '' }}"><span>3</span> Create a new password</li>
                </ol>
            </div>
        </div>
        <div class="auth-form-side">
            <div class="rec-card">
