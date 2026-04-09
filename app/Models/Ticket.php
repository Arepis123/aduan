<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'sector_id',
        'department_id',
        'category_id',
        'requester_name',
        'requester_email',
        'requester_phone',
        'complainant_company',
        'requester_type',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_at',
        'resolved_at',
        'closed_at',
        'closing_remark',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getDueDateAttribute(): ?\Carbon\Carbon
    {
        return $this->assigned_at?->addDays(7);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->assigned_at || in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }
        return now()->gt($this->due_date);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->assigned_at || in_array($this->status, ['resolved', 'closed'])) {
            return null;
        }
        return (int) now()->diffInDays($this->due_date, false);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $prefix = 'TKT';

        $lastTicket = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $newNumber);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_assignees')->withTimestamps();
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TicketLog::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'resolved' => 'green',
            'closed' => 'zinc',
            default => 'zinc',
        };
    }
}
