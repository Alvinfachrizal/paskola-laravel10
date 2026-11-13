<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id',
        'author_id',
        'title',
        'content',
        'target_roles',
        'target_class_ids',
        'is_pinned',
        'published_at',
        'expires_at',
        'priority',
    ];

    protected $casts = [
        'target_roles'    => 'array',
        'target_class_ids' => 'array',
        'is_pinned'       => 'boolean',
        'published_at'    => 'datetime',
        'expires_at'      => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /** Hanya pengumuman yang sudah dipublish dan belum expired */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', Carbon::now())
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', Carbon::now());
                     });
    }

    /** Filter berdasarkan role user */
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $role);
        });
    }

    /** Filter berdasarkan kelas siswa */
    public function scopeForClass(Builder $query, ?string $classId): Builder
    {
        if (!$classId) return $query;

        return $query->where(function ($q) use ($classId) {
            $q->whereNull('target_class_ids')
              ->orWhereJsonContains('target_class_ids', $classId);
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return is_null($this->published_at);
    }

    public function isPublished(): bool
    {
        return !is_null($this->published_at)
            && $this->published_at->lte(Carbon::now())
            && ($this->expires_at === null || $this->expires_at->gt(Carbon::now()));
    }

    public function isExpired(): bool
    {
        return !is_null($this->expires_at) && $this->expires_at->lte(Carbon::now());
    }

    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isDraft()) return 'Draft';
        if ($this->isExpired()) return 'Expired';
        return 'Aktif';
    }

    public function getStatusColorAttribute(): string
    {
        return match (true) {
            $this->isDraft()    => 'secondary',
            $this->isExpired()  => 'danger',
            default             => 'success',
        };
    }
}
