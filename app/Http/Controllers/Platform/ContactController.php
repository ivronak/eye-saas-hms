<?php

/**
 * ContactController.php
 *
 * PURPOSE: Public "Contact us" page + form submission (no auth).
 *
 * ROUTES:
 *   GET  /contact  → show
 *   POST /contact  → store (throttled; honeypot; JSON when called via fetch)
 */

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('landing.contact', [
            'types' => ContactInquiry::TYPES,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Honeypot: real users never see/fill this field. Bots do — pretend success.
        if (filled($request->input('website'))) {
            return $this->success($request);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'hospital_name' => ['nullable', 'string', 'max:190'],
            'inquiry_type' => ['required', 'in:' . implode(',', array_keys(ContactInquiry::TYPES))],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'phone.regex' => 'Enter a valid phone number.',
            'message.min' => 'Please write a little more so we can help (at least 10 characters).',
        ]);

        ContactInquiry::create([
            ...$validated,
            'status' => ContactInquiry::STATUS_NEW,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        return $this->success($request);
    }

    private function success(Request $request): JsonResponse|RedirectResponse
    {
        $message = 'Thank you! Your message has been sent. Our team will get back to you shortly.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('contact.show')->with('contact_success', $message);
    }
}
