<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #0D2137; }
    html, body { min-height: 100%; background: linear-gradient(135deg, #EBF5FB 0%, #fff 60%, #EBF5FB 100%); }
    .split-layout { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 28px; }
    .auth-shell { width: min(1100px, 100%); min-height: 620px; display: grid; grid-template-columns: 1fr 1fr; background: #fff;
        border: 1.5px solid rgba(41, 128, 185, .12); border-radius: 40px; box-shadow: 0 24px 60px rgba(13, 33, 55, .16); overflow: hidden; }
    .auth-banner { display: flex; align-items: center; justify-content: center; padding: 44px; border-right: 1px solid rgba(27, 79, 114, .12);
        background: linear-gradient(180deg, rgba(235, 245, 251, .85) 0%, rgba(255, 255, 255, .96) 100%); }
    .banner-content { text-align: center; max-width: 360px; }
    .banner-logo { height: 56px; width: auto; object-fit: contain; margin-bottom: 1.75rem; }
    .hero-icon { width: 120px; height: 120px; border-radius: 30px; margin: 0 auto 1.5rem; background: #fff; color: #1B4F72; font-size: 3rem;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 16px 40px rgba(27, 79, 114, .14); }
    .banner-content h2 { font-size: 1.4rem; font-weight: 800; letter-spacing: -.02em; margin: 0 0 .4rem; color: #0D2137; }
    .banner-content p { font-size: .9rem; color: #64748B; margin: 0 0 1.5rem; line-height: 1.5; }
    .banner-steps { list-style: none; margin: 0; padding: 0; text-align: left; display: inline-block; }
    .banner-steps li { display: flex; align-items: center; gap: .7rem; padding: .4rem 0; font-size: .875rem; color: #94A3B8; font-weight: 500; }
    .banner-steps li span { width: 24px; height: 24px; border-radius: 50%; background: #E5EDF4; color: #64748B; display: inline-flex;
        align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; }
    .banner-steps li.active { color: #0D2137; font-weight: 700; }
    .banner-steps li.active span { background: #1B4F72; color: #fff; }
    .banner-steps li.done { color: #374151; }
    .banner-steps li.done span { background: #27AE60; color: #fff; }
    .auth-form-side { display: flex; align-items: center; justify-content: center; padding: 44px; background: #fff; }
    .rec-card { width: 100%; max-width: 400px; }
    .rec-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; min-height: 34px; }
    .rec-back { width: 34px; height: 34px; border-radius: 50%; background: #F1F5F9; color: #475569; display: inline-flex;
        align-items: center; justify-content: center; text-decoration: none; font-size: .8rem; transition: background .15s; }
    .rec-back:hover { background: #E2E8F0; }
    .rec-step { font-size: .6875rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94A3B8; }
    .rec-icon { width: 56px; height: 56px; border-radius: 16px; background: #EAF4FB; color: #1B4F72; display: flex;
        align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 1rem; }
    .rec-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin: 0 0 .4rem; color: #0D2137; }
    .rec-sub { font-size: .875rem; line-height: 1.5; color: #64748B; margin: 0 0 1.5rem; }
    .rec-sub strong { color: #0D2137; font-weight: 600; word-break: break-all; }
    .rec-label { display: block; font-size: .8125rem; font-weight: 600; color: #374151; margin-bottom: .4rem; }
    .rec-field { position: relative; margin-bottom: 1.1rem; }
    .rec-field > i.lead { position: absolute; left: .95rem; top: 2.45rem; color: #94A3B8; font-size: .85rem; }
    .rec-input { width: 100%; height: 48px; padding: 0 .95rem 0 2.5rem; font-size: .9rem; font-family: inherit; color: #111827;
        border: 1.5px solid #D5DEE8; border-radius: 12px; background: #fff; outline: none; transition: border-color .2s, box-shadow .2s; }
    .rec-input.no-icon { padding-left: .95rem; }
    .rec-input:focus { border-color: #2980B9; box-shadow: 0 0 0 4px rgba(41, 128, 185, .12); }
    .rec-input.is-error { border-color: #C0392B; }
    .rec-hint { font-size: .75rem; color: #94A3B8; margin-top: .4rem; }
    .rec-btn { width: 100%; height: 48px; border: none; border-radius: 12px; background: #1B4F72; color: #fff; font-family: inherit;
        font-size: .9375rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem;
        box-shadow: 0 6px 16px rgba(27, 79, 114, .22); transition: background .15s, transform .1s; }
    .rec-btn:hover { background: #154360; }
    .rec-btn:active { transform: scale(.99); }
    .rec-btn:disabled { opacity: .7; cursor: not-allowed; }
    .rec-alert { display: flex; gap: .55rem; align-items: flex-start; padding: .7rem .9rem; border-radius: 12px; margin-bottom: 1.1rem;
        font-size: .8125rem; line-height: 1.4; }
    .rec-alert i { margin-top: .12rem; }
    .rec-alert.danger { background: #FDEDEC; color: #922B21; }
    .rec-alert.success { background: #E9F7EF; color: #1E6B42; }
    /* OTP */
    .otp-code { display: flex; align-items: center; justify-content: space-between; gap: .75rem; background: #F4F9FD;
        border: 1px dashed #9CC5E3; border-radius: 12px; padding: .7rem .9rem; margin-bottom: 1.25rem; }
    .otp-code small { display: block; font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748B; }
    .otp-code strong { font-size: 1.25rem; letter-spacing: .3em; color: #1B4F72; }
    .otp-chip { display: inline-flex; align-items: center; gap: .35rem; border: none; background: #fff; color: #1B4F72;
        border: 1px solid #D5DEE8; border-radius: 8px; padding: .4rem .7rem; font-size: .75rem; font-weight: 600; cursor: pointer; font-family: inherit; }
    .otp-chip:hover { background: #EAF4FB; }
    .otp-boxes { display: grid; grid-template-columns: repeat(6, 1fr); gap: .5rem; margin-bottom: 1.25rem; }
    .otp-box { width: 100%; height: 54px; text-align: center; font-size: 1.35rem; font-weight: 700; font-family: inherit; color: #0D2137;
        border: 1.5px solid #D5DEE8; border-radius: 12px; outline: none; background: #fff; transition: border-color .2s, box-shadow .2s; padding: 0; }
    .otp-box:focus { border-color: #2980B9; box-shadow: 0 0 0 4px rgba(41, 128, 185, .12); }
    .otp-box.filled { border-color: #1B4F72; background: #F4F9FD; }
    .otp-boxes.is-error .otp-box { border-color: #C0392B; background: #FEF5F4; }
    .rec-resend { text-align: center; margin-top: 1.1rem; font-size: .8125rem; color: #64748B; }
    .rec-link-btn { background: none; border: none; padding: 0; font: inherit; font-weight: 700; color: #2980B9; cursor: pointer; }
    .rec-link-btn:disabled { color: #94A3B8; cursor: not-allowed; }
    .rec-foot { text-align: center; margin-top: 1.25rem; font-size: .8125rem; }
    .rec-foot a { color: #64748B; text-decoration: none; }
    .rec-foot a:hover { color: #1B4F72; }
    @media (max-width: 900px) {
        .auth-shell { grid-template-columns: 1fr; min-height: auto; border-radius: 28px; }
        .auth-banner { display: none; }
        .auth-form-side { padding: 32px 22px; }
    }
    @media (max-width: 480px) {
        .split-layout { padding: 14px; }
        .otp-boxes { gap: .35rem; }
        .otp-box { height: 48px; font-size: 1.15rem; }
    }
</style>
