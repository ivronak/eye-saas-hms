@extends('superadmin.layouts.app')
@section('title', 'Contact Inquiries')
@section('page-header', 'Contact Inquiries')

@php
    $statusStyle = [
        'new' => 'background:#FDEBD0;color:#784212',
        'read' => 'background:#D6EAF8;color:#154360',
        'replied' => 'background:#D5F5E3;color:#1A6F5B',
        'closed' => 'background:#F4F6F7;color:#2C3E50',
    ];
    $activeStatus = request('status');
@endphp

@section('page-actions')
    @if(($counts['new'] ?? 0) > 0)
        <form method="POST" action="{{ route('superadmin.contact-inquiries.mark-all-read') }}" style="display:inline">
            @csrf
            <button type="submit" class="hms-btn hms-btn-outline hms-btn-sm">
                <i class="bi bi-check2-all"></i> Mark all as read
            </button>
        </form>
    @endif
@endsection

@section('content')

    {{-- Status tabs --}}
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem">
        <a href="{{ route('superadmin.contact-inquiries.index') }}"
            class="hms-btn {{ $activeStatus ? 'hms-btn-outline' : 'hms-btn-primary' }} hms-btn-sm"
            style="{{ $activeStatus ? '' : 'color:#1B4F72' }}">
            All <span class="hms-badge hms-badge-info" style="margin-left:.35rem">{{ $allCount }}</span>
        </a>
        @foreach(\App\Models\Platform\ContactInquiry::STATUSES as $key => $label)
            <a href="{{ route('superadmin.contact-inquiries.index', ['status' => $key]) }}"
                class="hms-btn {{ $activeStatus === $key ? 'hms-btn-primary' : 'hms-btn-outline' }} hms-btn-sm"
                style="{{ $activeStatus === $key ? 'color:#1B4F72' : '' }}">
                {{ $label }}
                <span class="hms-badge" style="margin-left:.35rem;{{ $statusStyle[$key] }}">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="hms-card" style="padding:1.25rem;margin-bottom:1.25rem">
        <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
            @if($activeStatus)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <div class="hms-form-group" style="margin-bottom:0;flex:1;min-width:220px">
                <label class="hms-label">Search</label>
                <input type="text" name="q" class="hms-input" value="{{ request('q') }}"
                    placeholder="Name, email, phone, hospital or message">
            </div>
            <div class="hms-form-group" style="margin-bottom:0;min-width:200px">
                <label class="hms-label">Type</label>
                <select name="type" class="hms-select">
                    <option value="">All types</option>
                    @foreach(\App\Models\Platform\ContactInquiry::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="submit" class="hms-btn hms-btn-primary hms-btn-sm" style="color:#1b4f72;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <a href="{{ route('superadmin.contact-inquiries.index') }}" class="hms-btn hms-btn-outline hms-btn-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="hms-card" style="padding:0">
        <div class="hms-card-header">
            <h3 class="hms-card-title">
                <i class="bi bi-envelope-paper-fill" style="color:#1B4F72"></i>
                Inquiries
            </h3>
            <span class="hms-badge hms-badge-info">{{ $inquiries->total() }} total</span>
        </div>

        <div class="hms-table-wrap" style="border:none">
            <table class="hms-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inq)
                        <tr style="{{ $inq->status === 'new' ? 'background:rgba(15,79,134,.04)' : '' }}">
                            <td style="color:#94A3B8;font-size:.75rem">{{ $inq->id }}</td>
                            <td>
                                <a href="{{ route('superadmin.contact-inquiries.show', $inq) }}"
                                    style="font-weight:{{ $inq->status === 'new' ? '700' : '600' }};color:#1A202C">{{ $inq->name }}</a>
                                <div style="font-size:.75rem;color:#64748B">{{ $inq->email }}@if($inq->phone) · {{ $inq->phone }}@endif</div>
                                @if($inq->hospital_name)
                                    <div style="font-size:.72rem;color:#94A3B8">{{ $inq->hospital_name }}</div>
                                @endif
                            </td>
                            <td><span class="hms-badge" style="font-size:.7rem;background:#F4F6F7;color:#2C3E50">{{ $inq->typeLabel() }}</span></td>
                            <td style="font-size:.85rem;max-width:300px;color:#334155">
                                @if($inq->subject)<div style="font-weight:600">{{ $inq->subject }}</div>@endif
                                {{ \Illuminate\Support\Str::limit($inq->message, 90) }}
                            </td>
                            <td>
                                <span class="hms-badge" style="font-size:.7rem;{{ $statusStyle[$inq->status] ?? $statusStyle['closed'] }}">{{ $inq->statusLabel() }}</span>
                            </td>
                            <td style="white-space:nowrap;font-size:.82rem;color:#475569">
                                {{ $inq->created_at->format('d M Y') }}
                                <div style="font-size:.72rem;color:#94A3B8">{{ $inq->created_at->format('h:i A') }}</div>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <a href="{{ route('superadmin.contact-inquiries.show', $inq) }}" class="hms-btn hms-btn-outline hms-btn-sm">
                                    <i class="bi bi-eye-fill"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <x-empty-state icon="bi bi-envelope-paper" title="No inquiries yet"
                                    description="Messages sent from the public Contact page will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div style="padding:1rem;border-top:1px solid rgba(27,79,114,.1)">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>

@endsection
