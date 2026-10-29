@extends('superadmin.layouts.app')
@section('title', 'Inquiry from ' . $inquiry->name)
@section('page-header', 'Contact Inquiry')

@php
    $statusStyle = [
        'new' => 'background:#FDEBD0;color:#784212',
        'read' => 'background:#D6EAF8;color:#154360',
        'replied' => 'background:#D5F5E3;color:#1A6F5B',
        'closed' => 'background:#F4F6F7;color:#2C3E50',
    ];
    $subject = $inquiry->subject ?: 'Your EYENOSIS enquiry';
@endphp

@section('page-actions')
    <a href="{{ route('superadmin.contact-inquiries.index') }}" class="hms-btn hms-btn-outline hms-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <a href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Re: ' . $subject) }}"
        class="hms-btn hms-btn-primary hms-btn-sm" style="color:#1B4F72">
        <i class="bi bi-reply-fill"></i> Reply by email
    </a>
@endsection

@section('content')
    <div style="display:grid;grid-template-columns:minmax(0,7fr) minmax(0,4fr);gap:1.25rem;align-items:start" class="ci-grid">

        {{-- Message --}}
        <div class="hms-card" style="padding:0">
            <div class="hms-card-header">
                <h3 class="hms-card-title"><i class="bi bi-chat-left-text-fill" style="color:#1B4F72"></i> Message</h3>
                <span class="hms-badge" style="{{ $statusStyle[$inquiry->status] ?? $statusStyle['closed'] }}">{{ $inquiry->statusLabel() }}</span>
            </div>
            <div style="padding:1.25rem 1.5rem">
                <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem">
                    <span class="hms-badge" style="background:#F4F6F7;color:#2C3E50">{{ $inquiry->typeLabel() }}</span>
                    <span style="font-size:.8rem;color:#64748B">Received {{ $inquiry->created_at->format('d M Y, h:i A') }}</span>
                </div>
                @if($inquiry->subject)
                    <h4 style="margin:0 0 .75rem;font-size:1.1rem;color:#1A202C">{{ $inquiry->subject }}</h4>
                @endif
                <div style="white-space:pre-wrap;line-height:1.7;color:#334155;font-size:.95rem">{{ $inquiry->message }}</div>
            </div>
        </div>

        {{-- Sender + handling --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">
            <div class="hms-card" style="padding:0">
                <div class="hms-card-header">
                    <h3 class="hms-card-title"><i class="bi bi-person-lines-fill" style="color:#1B4F72"></i> Sender</h3>
                </div>
                <div style="padding:1rem 1.5rem;font-size:.9rem;display:grid;gap:.75rem">
                    <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8">Name</div><strong>{{ $inquiry->name }}</strong></div>
                    <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8">Email</div><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
                    @if($inquiry->phone)
                        <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8">Phone</div><a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->phone) }}">{{ $inquiry->phone }}</a></div>
                    @endif
                    @if($inquiry->hospital_name)
                        <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8">Hospital / clinic</div>{{ $inquiry->hospital_name }}</div>
                    @endif
                    <div style="font-size:.72rem;color:#94A3B8;border-top:1px solid rgba(27,79,114,.08);padding-top:.75rem">
                        IP {{ $inquiry->ip_address ?? '—' }}
                        @if($inquiry->read_at)<br>Read {{ $inquiry->read_at->format('d M Y, h:i A') }}@endif
                        @if($inquiry->replied_at)<br>Replied {{ $inquiry->replied_at->format('d M Y, h:i A') }}@endif
                        @if($inquiry->handler)<br>Handled by {{ $inquiry->handler->name }}@endif
                    </div>
                </div>
            </div>

            <div class="hms-card" style="padding:0">
                <div class="hms-card-header">
                    <h3 class="hms-card-title"><i class="bi bi-sliders" style="color:#1B4F72"></i> Handling</h3>
                </div>
                <form method="POST" action="{{ route('superadmin.contact-inquiries.update', $inquiry) }}" style="padding:1rem 1.5rem">
                    @csrf
                    @method('PUT')
                    <div class="hms-form-group">
                        <label class="hms-label">Status</label>
                        <select name="status" class="hms-select">
                            @foreach(\App\Models\Platform\ContactInquiry::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ $inquiry->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hms-form-group">
                        <label class="hms-label">Internal notes</label>
                        <textarea name="admin_notes" class="hms-input" rows="4" maxlength="3000"
                            placeholder="Only visible to platform admins">{{ old('admin_notes', $inquiry->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="hms-btn hms-btn-primary hms-btn-sm" style="color:#1B4F72">
                        <i class="bi bi-check2-circle"></i> Save
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('superadmin.contact-inquiries.destroy', $inquiry) }}"
                onsubmit="return confirm('Delete this inquiry permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="hms-btn hms-btn-outline hms-btn-sm" style="color:#c0392b;border-color:#f1c0bb">
                    <i class="bi bi-trash-fill"></i> Delete inquiry
                </button>
            </form>
        </div>
    </div>

    <style>
        @media (max-width: 992px) { .ci-grid { grid-template-columns: 1fr !important; } }
    </style>
@endsection
