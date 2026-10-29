@extends('landing.layouts.app')

@section('title', 'Contact Us — EYENOSIS | Eye Hospital CRM')
@section('meta_description', 'Talk to the EYENOSIS team — request a demo, ask about pricing or get support for your eye hospital CRM.')
@section('og_title', 'Contact EYENOSIS')

@php
    $supportEmail = platform_setting('support_email');
    $supportPhone = platform_setting('support_phone');
    $supportAddress = platform_setting('support_address');
@endphp

@push('styles')
    <style>
        .ct-wrap {
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
            gap: clamp(1.25rem, 3vw, 2.25rem);
            align-items: start;
        }

        /* ── Details side ── */
        .ct-info {
            position: relative;
            background: linear-gradient(160deg, var(--ecrm-blue-deep), #0a3a64);
            color: #fff;
            border-radius: 24px;
            padding: clamp(1.5rem, 3vw, 2.4rem);
            box-shadow: var(--ecrm-shadow);
            overflow: hidden;
        }
        .ct-info::after {
            content: "";
            position: absolute;
            right: -70px;
            bottom: -70px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .07);
            pointer-events: none;
        }
        .ct-info h2 {
            font-family: var(--ecrm-display);
            font-size: clamp(1.4rem, 2.4vw, 1.8rem);
            margin: 0 0 .5rem;
            font-weight: 600;
            color: #fff;
        }
        .ct-info > p {
            margin: 0 0 1.6rem;
            color: rgba(255, 255, 255, .78);
            line-height: 1.6;
        }
        .ct-item {
            display: flex;
            gap: .9rem;
            align-items: flex-start;
            padding: .85rem 0;
            border-top: 1px solid rgba(255, 255, 255, .14);
        }
        .ct-item:first-of-type { border-top: 0; }
        .ct-ico {
            flex: 0 0 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .14);
            font-size: 1rem;
        }
        .ct-item small {
            display: block;
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .6);
            margin-bottom: .15rem;
        }
        .ct-item a, .ct-item span.v {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            word-break: break-word;
        }
        .ct-item a:hover { text-decoration: underline; }
        .ct-links {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-top: 1.4rem;
            position: relative;
            z-index: 1;
        }
        .ct-links a {
            padding: .5rem .95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s;
        }
        .ct-links a:hover { background: rgba(255, 255, 255, .24); }

        /* ── Form side ── */
        .ct-card {
            background: var(--ecrm-white);
            border: 1px solid var(--ecrm-border);
            border-radius: 24px;
            padding: clamp(1.4rem, 3vw, 2.4rem);
            box-shadow: var(--ecrm-shadow-soft);
        }
        .ct-card h2 {
            font-family: var(--ecrm-display);
            font-size: clamp(1.4rem, 2.4vw, 1.8rem);
            margin: 0 0 .35rem;
            color: var(--ecrm-navy);
            font-weight: 600;
        }
        .ct-card > p { margin: 0 0 1.4rem; color: var(--ecrm-muted); }
        .ct-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .ct-field { display: flex; flex-direction: column; gap: .35rem; margin-bottom: 1rem; }
        .ct-field.full { grid-column: 1 / -1; }
        .ct-field label {
            font-size: .82rem;
            font-weight: 700;
            color: var(--ecrm-ink);
        }
        .ct-field label .req { color: #c0392b; }
        .ct-field input, .ct-field select, .ct-field textarea {
            width: 100%;
            font: inherit;
            font-size: .95rem;
            color: var(--ecrm-ink);
            background: var(--ecrm-mist);
            border: 1.5px solid transparent;
            border-radius: 14px;
            padding: .8rem 1rem;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .ct-field textarea { min-height: 140px; resize: vertical; }
        .ct-field input:focus, .ct-field select:focus, .ct-field textarea:focus {
            outline: none;
            background: #fff;
            border-color: var(--ecrm-blue);
            box-shadow: 0 0 0 4px rgba(26, 111, 181, .12);
        }
        .ct-field.has-error input, .ct-field.has-error select, .ct-field.has-error textarea {
            border-color: #c0392b;
            background: #fff7f6;
        }
        .ct-err { display: none; font-size: .78rem; color: #c0392b; font-weight: 600; }
        .ct-field.has-error .ct-err { display: block; }
        .ct-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ct-alert {
            display: none;
            align-items: flex-start;
            gap: .7rem;
            padding: .9rem 1rem;
            border-radius: 14px;
            margin-bottom: 1.1rem;
            font-weight: 600;
            font-size: .92rem;
        }
        .ct-alert.show { display: flex; }
        .ct-alert.ok { background: #e7f7ef; color: #14724a; }
        .ct-alert.bad { background: #fdecea; color: #a5281b; }
        .ct-submit { margin-top: .25rem; }
        .ct-submit .ecrm-btn[disabled] { opacity: .7; cursor: not-allowed; }
        .ct-note { margin: .9rem 0 0; font-size: .8rem; color: var(--ecrm-muted); text-align: center; }

        @media (max-width: 900px) {
            .ct-wrap { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .ct-grid { grid-template-columns: 1fr; }
            .ct-info, .ct-card { border-radius: 20px; }
        }
    </style>
@endpush

@section('content')

    <section class="ecrm-page-hero">
        <div class="ecrm-container ecrm-reveal">
            <span class="ecrm-kicker">Contact</span>
            <h1>We'd love to hear from you</h1>
            <p>Questions about EYENOSIS, a live demo for your hospital, or help with your account — send us a message and
                our team will reply.</p>
        </div>
    </section>

    <section class="ecrm-section">
        <div class="ecrm-container">
            <div class="ct-wrap">

                {{-- Details --}}
                <aside class="ct-info ecrm-reveal">
                    <h2>Get in touch</h2>
                    <p>Pick whichever way suits you — we usually reply within one business day.</p>

                    @if($supportEmail)
                        <div class="ct-item">
                            <span class="ct-ico"><i class="fa-solid fa-envelope"></i></span>
                            <div><small>Email</small><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></div>
                        </div>
                    @endif
                    @if($supportPhone)
                        <div class="ct-item">
                            <span class="ct-ico"><i class="fa-solid fa-phone"></i></span>
                            <div><small>Phone</small><a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}">{{ $supportPhone }}</a></div>
                        </div>
                    @endif
                    @if($supportAddress)
                        <div class="ct-item">
                            <span class="ct-ico"><i class="fa-solid fa-location-dot"></i></span>
                            <div><small>Address</small><span class="v">{{ $supportAddress }}</span></div>
                        </div>
                    @endif
                    <div class="ct-item">
                        <span class="ct-ico"><i class="fa-solid fa-globe"></i></span>
                        <div><small>Website</small><a href="https://eyenosis.com" target="_blank" rel="noopener noreferrer">eyenosis.com</a></div>
                    </div>

                    <div class="ct-links">
                        <a href="{{ route('pricing') }}">View pricing</a>
                        <a href="{{ route('register.show') }}">Start free trial</a>
                        <a href="{{ route('home') }}#faq">Read FAQ</a>
                    </div>
                </aside>

                {{-- Form --}}
                <div class="ct-card ecrm-reveal">
                    <h2>Send us a message</h2>
                    <p>Fields marked <span style="color:#c0392b">*</span> are required.</p>

                    <div class="ct-alert ok {{ session('contact_success') ? 'show' : '' }}" id="ctOk" role="status">
                        <i class="fa-solid fa-circle-check"></i>
                        <span id="ctOkText">{{ session('contact_success') }}</span>
                    </div>
                    <div class="ct-alert bad" id="ctBad" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span id="ctBadText"></span>
                    </div>

                    <form id="ctForm" method="POST" action="{{ route('contact.store') }}" novalidate>
                        @csrf
                        <div class="ct-hp" aria-hidden="true">
                            <label for="ct_website">Website</label>
                            <input type="text" id="ct_website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="ct-grid">
                            <div class="ct-field @error('name') has-error @enderror">
                                <label for="ct_name">Full name <span class="req">*</span></label>
                                <input type="text" id="ct_name" name="name" value="{{ old('name') }}" maxlength="120"
                                    autocomplete="name" required>
                                <span class="ct-err" data-err="name">@error('name'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field @error('email') has-error @enderror">
                                <label for="ct_email">Email <span class="req">*</span></label>
                                <input type="email" id="ct_email" name="email" value="{{ old('email') }}" maxlength="190"
                                    autocomplete="email" required>
                                <span class="ct-err" data-err="email">@error('email'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field @error('phone') has-error @enderror">
                                <label for="ct_phone">Phone</label>
                                <input type="tel" id="ct_phone" name="phone" value="{{ old('phone') }}" maxlength="30"
                                    autocomplete="tel" placeholder="+91 98765 43210">
                                <span class="ct-err" data-err="phone">@error('phone'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field @error('hospital_name') has-error @enderror">
                                <label for="ct_hospital">Hospital / clinic</label>
                                <input type="text" id="ct_hospital" name="hospital_name" value="{{ old('hospital_name') }}"
                                    maxlength="190" autocomplete="organization">
                                <span class="ct-err" data-err="hospital_name">@error('hospital_name'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field @error('inquiry_type') has-error @enderror">
                                <label for="ct_type">I'm interested in <span class="req">*</span></label>
                                <select id="ct_type" name="inquiry_type" required>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" @selected(old('inquiry_type', 'general') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="ct-err" data-err="inquiry_type">@error('inquiry_type'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field @error('subject') has-error @enderror">
                                <label for="ct_subject">Subject</label>
                                <input type="text" id="ct_subject" name="subject" value="{{ old('subject') }}" maxlength="190">
                                <span class="ct-err" data-err="subject">@error('subject'){{ $message }}@enderror</span>
                            </div>
                            <div class="ct-field full @error('message') has-error @enderror">
                                <label for="ct_message">Message <span class="req">*</span></label>
                                <textarea id="ct_message" name="message" maxlength="3000" required>{{ old('message') }}</textarea>
                                <span class="ct-err" data-err="message">@error('message'){{ $message }}@enderror</span>
                            </div>
                        </div>

                        <div class="ct-submit">
                            <button type="submit" class="ecrm-btn ecrm-btn-primary ecrm-btn-lg ecrm-btn-block" id="ctBtn">
                                <i class="fa-solid fa-paper-plane"></i> <span id="ctBtnText">Send message</span>
                            </button>
                        </div>
                        <p class="ct-note">We'll only use your details to reply to this enquiry.</p>
                    </form>
                </div>

            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('ctForm');
            if (!form || !window.fetch) return; // falls back to a normal POST + redirect

            var btn = document.getElementById('ctBtn');
            var btnText = document.getElementById('ctBtnText');
            var ok = document.getElementById('ctOk');
            var okText = document.getElementById('ctOkText');
            var bad = document.getElementById('ctBad');
            var badText = document.getElementById('ctBadText');

            function clearErrors() {
                form.querySelectorAll('.ct-field').forEach(function (f) { f.classList.remove('has-error'); });
                form.querySelectorAll('.ct-err').forEach(function (e) { e.textContent = ''; });
                bad.classList.remove('show');
            }

            function showErrors(errors) {
                Object.keys(errors).forEach(function (name) {
                    var holder = form.querySelector('[data-err="' + name + '"]');
                    if (!holder) return;
                    holder.textContent = errors[name][0];
                    holder.closest('.ct-field').classList.add('has-error');
                });
                var first = form.querySelector('.ct-field.has-error input, .ct-field.has-error select, .ct-field.has-error textarea');
                if (first) first.focus();
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors();
                ok.classList.remove('show');
                btn.disabled = true;
                btnText.textContent = 'Sending…';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    },
                    body: new FormData(form)
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        return { status: res.status, data: data };
                    });
                }).then(function (r) {
                    if (r.status === 422 && r.data.errors) {
                        showErrors(r.data.errors);
                    } else if (r.status === 429) {
                        badText.textContent = 'Too many attempts. Please wait a minute and try again.';
                        bad.classList.add('show');
                    } else if (r.data && r.data.success) {
                        okText.textContent = r.data.message;
                        ok.classList.add('show');
                        form.reset();
                        ok.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        badText.textContent = 'Something went wrong. Please try again.';
                        bad.classList.add('show');
                    }
                }).catch(function () {
                    badText.textContent = 'Network error. Please check your connection and try again.';
                    bad.classList.add('show');
                }).finally(function () {
                    btn.disabled = false;
                    btnText.textContent = 'Send message';
                });
            });
        })();
    </script>
@endpush
