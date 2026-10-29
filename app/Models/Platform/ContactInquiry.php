<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ContactInquiry — Platform Model
 *
 * A submission from the public "Contact us" page. Not tenant-scoped.
 *
 * TABLE: contact_inquiries
 */
class ContactInquiry extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_READ => 'Read',
        self::STATUS_REPLIED => 'Replied',
        self::STATUS_CLOSED => 'Closed',
    ];

    public const TYPES = [
        'general' => 'General question',
        'demo' => 'Request a demo',
        'pricing' => 'Pricing & plans',
        'support' => 'Support',
        'partnership' => 'Partnership',
        'other' => 'Other',
    ];

    protected $table = 'contact_inquiries';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'hospital_name',
        'inquiry_type',
        'subject',
        'message',
        'status',
        'read_at',
        'replied_at',
        'handled_by',
        'admin_notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'handled_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /** Count shown on the Super Admin sidebar badge. */
    public static function unreadCount(): int
    {
        try {
            return static::query()->unread()->count();
        } catch (\Throwable) {
            // Table not migrated yet — never break the Super Admin layout over a badge.
            return 0;
        }
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->inquiry_type] ?? ucfirst((string) $this->inquiry_type);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }
}
