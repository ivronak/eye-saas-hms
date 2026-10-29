<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactInquiry::query()->latest();

        if ($status = $request->query('status')) {
            if (array_key_exists($status, ContactInquiry::STATUSES)) {
                $query->where('status', $status);
            }
        }

        if ($type = $request->query('type')) {
            if (array_key_exists($type, ContactInquiry::TYPES)) {
                $query->where('inquiry_type', $type);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('hospital_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->paginate(20)->withQueryString();

        $counts = ContactInquiry::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('superadmin.contact-inquiries.index', [
            'inquiries' => $inquiries,
            'counts' => $counts,
            'allCount' => (int) $counts->sum(),
        ]);
    }

    public function show(ContactInquiry $inquiry): View
    {
        // Opening a new inquiry marks it as read (clears it from the sidebar badge).
        if ($inquiry->status === ContactInquiry::STATUS_NEW) {
            $inquiry->update([
                'status' => ContactInquiry::STATUS_READ,
                'read_at' => now(),
                'handled_by' => auth('superadmin')->id(),
            ]);
        }

        return view('superadmin.contact-inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(ContactInquiry::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $data = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'handled_by' => auth('superadmin')->id(),
        ];

        if ($validated['status'] === ContactInquiry::STATUS_REPLIED && ! $inquiry->replied_at) {
            $data['replied_at'] = now();
        }
        if ($validated['status'] !== ContactInquiry::STATUS_NEW && ! $inquiry->read_at) {
            $data['read_at'] = now();
        }

        $inquiry->update($data);

        return redirect()
            ->route('superadmin.contact-inquiries.show', $inquiry)
            ->with('success', 'Inquiry updated.');
    }

    public function markAllRead(): RedirectResponse
    {
        ContactInquiry::query()->unread()->update([
            'status' => ContactInquiry::STATUS_READ,
            'read_at' => now(),
            'handled_by' => auth('superadmin')->id(),
        ]);

        return redirect()
            ->route('superadmin.contact-inquiries.index')
            ->with('success', 'All new inquiries marked as read.');
    }

    public function destroy(ContactInquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()
            ->route('superadmin.contact-inquiries.index')
            ->with('success', 'Inquiry deleted.');
    }
}
