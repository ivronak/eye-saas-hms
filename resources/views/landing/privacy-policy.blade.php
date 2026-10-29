@extends('landing.layouts.app')

@section('title', 'Privacy Policy — EYENOSIS | Eye Hospital CRM Platform')
@section('meta_description')
How EYENOSIS collects, uses, secures and shares data across the EYENOSIS eye hospital CRM platform — web, Android, iOS and tablet.
@endsection
@section('og_title', 'Privacy Policy — EYENOSIS')
@section('og_description', 'Read how EYENOSIS handles hospital, staff and patient data across our CRM platform.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/eye-crm-legal.css') }}">
@endpush

@section('content')

    <section class="ecrm-page-hero">
        <div class="ecrm-container ecrm-reveal">
            <span class="ecrm-kicker">Legal</span>
            <h1>Privacy Policy</h1>
            <p>Last updated: {{ now()->format('d F Y') }}. This policy explains what data EYENOSIS collects,
                how it is used, and how hospitals and patients are protected on our platform.</p>
        </div>
    </section>

    <section class="ecrm-section ecrm-legal-section">
        <div class="ecrm-container">
            <div class="ecrm-legal-shell">

                <aside class="ecrm-legal-toc ecrm-reveal" aria-label="Table of contents">
                    <span class="ecrm-legal-toc-label">On this page</span>
                    <nav>
                        <a href="#overview">1. Overview</a>
                        <a href="#information-we-collect">2. Information we collect</a>
                        <a href="#patient-data">3. Patient data &amp; hospital responsibility</a>
                        <a href="#how-we-use">4. How we use information</a>
                        <a href="#sharing">5. Sharing &amp; third parties</a>
                        <a href="#security">6. Data security</a>
                        <a href="#retention">7. Data retention</a>
                        <a href="#rights">8. Your rights &amp; choices</a>
                        <a href="#cookies">9. Cookies &amp; tracking</a>
                        <a href="#children">10. Children's privacy</a>
                        <a href="#changes">11. Changes to this policy</a>
                        <a href="#contact">12. Contact us</a>
                    </nav>
                </aside>

                <article class="ecrm-legal-body ecrm-reveal">

                    <h2 id="overview">1. Overview</h2>
                    <p>EYENOSIS ("EYENOSIS", "we", "us", "our") provides a multi-tenant cloud CRM platform for eye
                        hospitals and clinics, covering reception, patient registration, clinical examination, OT
                        counselling, surgery, billing, discharge and reporting — accessible on web, Android, iOS and
                        tablet.</p>
                    <p>This Privacy Policy applies to the EYENOSIS marketing website (eyenosis.com), the hospital CRM
                        application, and our mobile and tablet apps (together, the "Platform"). By using the
                        Platform, you agree to the practices described here.</p>

                    <h2 id="information-we-collect">2. Information we collect</h2>
                    <p>We collect information in three broad categories:</p>
                    <ul>
                        <li><strong>Hospital &amp; account information</strong> — hospital/clinic name, admin
                            contact details, staff accounts, roles and permissions, and subscription/billing
                            details, provided when a hospital registers for EYENOSIS or when staff accounts are
                            created.</li>
                        <li><strong>Operational &amp; patient data</strong> — patient demographics, appointments,
                            examination findings, prescriptions, OT and surgery records, billing entries, and
                            attachments (reports, photos) that hospital staff enter into the Platform while
                            delivering care.</li>
                        <li><strong>Technical &amp; usage data</strong> — log-in activity, device and browser
                            information, IP address, and app usage patterns, used to keep the Platform secure and to
                            improve performance.</li>
                    </ul>

                    <h2 id="patient-data">3. Patient data &amp; hospital responsibility</h2>
                    <p>EYENOSIS is a <strong>data processor</strong> for all patient and clinical data entered by a
                        hospital. The hospital that registers on EYENOSIS remains the <strong>data controller</strong>
                        for its patients' records — it decides what patient data is collected, who on its staff can
                        access it, and how long it is retained, using the roles and permissions available inside the
                        Platform.</p>
                    <p>We process this data only to operate the Platform on the hospital's behalf (storage, backups,
                        display, printing, reporting) and do not use patient data for advertising, nor do we sell
                        patient data to any third party.</p>

                    <h2 id="how-we-use">4. How we use information</h2>
                    <ul>
                        <li>To provide, maintain and secure the EYENOSIS Platform for each registered hospital.</li>
                        <li>To authenticate staff logins and enforce role-based access within a hospital's own
                            workspace.</li>
                        <li>To process subscription payments and send related invoices and renewal reminders.</li>
                        <li>To send service communications — account, billing, security and support-related emails
                            or notifications.</li>
                        <li>To monitor, troubleshoot and improve Platform performance and reliability.</li>
                        <li>To comply with applicable law, or to respond to a valid legal request.</li>
                    </ul>

                    <h2 id="sharing">5. Sharing &amp; third parties</h2>
                    <p>We do not sell personal or patient data. We share data only with service providers that help
                        us run the Platform, under confidentiality obligations, including:</p>
                    <ul>
                        <li><strong>Cloud hosting &amp; storage providers</strong>, to host the application, database
                            and file/report uploads.</li>
                        <li><strong>Payment gateway providers</strong>, to process subscription payments securely —
                            EYENOSIS does not store full card or payment credentials.</li>
                        <li><strong>Email/SMS/notification providers</strong>, to deliver account and service
                            communications.</li>
                    </ul>
                    <p>We may also disclose information if required by law, regulation, legal process, or to protect
                        the rights, property or safety of EYENOSIS, our hospitals, or the public.</p>

                    <h2 id="security">6. Data security</h2>
                    <p>We use industry-standard safeguards to protect data on the Platform, including encrypted
                        connections (HTTPS) for data in transit, role-based access control so staff only see what
                        their permissions allow, and regular backups. No method of transmission or storage is 100%
                        secure, but we work to protect data using commercially reasonable means appropriate to a
                        healthcare CRM.</p>

                    <h2 id="retention">7. Data retention</h2>
                    <p>We retain hospital account data for as long as the hospital's subscription is active, and
                        patient/clinical data for as long as the hospital configures and retains it on the Platform,
                        consistent with applicable medical record-keeping requirements. When a hospital's
                        subscription ends, data may be retained for a reasonable backup/recovery period before
                        deletion, unless a longer period is required by law.</p>

                    <h2 id="rights">8. Your rights &amp; choices</h2>
                    <p>Hospital administrators can access, correct, export or delete records within their own
                        workspace using the roles and tools available in the Platform. Patients who wish to access,
                        correct, or request removal of their personal data should contact the hospital that treated
                        them, as the hospital controls that data; EYENOSIS supports the hospital in fulfilling such
                        requests. You may also contact us directly using the details in Section 12, and we will
                        route your request appropriately.</p>

                    <h2 id="cookies">9. Cookies &amp; tracking</h2>
                    <p>The EYENOSIS website and application use essential cookies and similar technologies to keep
                        you signed in, remember preferences, and keep the Platform secure (for example, session and
                        CSRF-protection cookies). We do not use these cookies for third-party advertising.</p>

                    <h2 id="children">10. Children's privacy</h2>
                    <p>The EYENOSIS Platform is intended for use by hospital staff and administrators, not directly
                        by children. Patient records for minors may be entered by hospital staff as part of clinical
                        care, under the hospital's own consent and data-protection practices.</p>

                    <h2 id="changes">11. Changes to this policy</h2>
                    <p>We may update this Privacy Policy from time to time to reflect changes to the Platform or
                        applicable law. The "Last updated" date at the top of this page will change when we do. We
                        encourage you to review this page periodically.</p>

                    <h2 id="contact">12. Contact us</h2>
                    @php($supportEmail = platform_setting('support_email', 'support@eyehms.com'))
                    <p>If you have questions about this Privacy Policy or how your data is handled, contact us at
                        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>

                </article>
            </div>
        </div>
    </section>

@endsection
