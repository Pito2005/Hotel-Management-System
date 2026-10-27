<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    // A log belongs to the staff/admin who performed it
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Reusable helper for recording staff actions
    public static function record(
        string $action,
        ?User $actor = null,
        ?string $description = null,
        ?Model $subject = null,
        array $details = []
    ): self {
        return self::create([
            'user_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject
                ? class_basename($subject)
                : null,
            'subject_id' => $subject?->getKey(),
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => substr(
                (string) request()->userAgent(),
                0,
                500
            ),
        ]);
    }
}
