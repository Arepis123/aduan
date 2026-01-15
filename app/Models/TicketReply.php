<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_client_reply',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'is_client_reply' => 'boolean',
            'is_internal_note' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->is_client_reply) {
            return $this->ticket->requester_name;
        }

        return $this->user?->name ?? 'System';
    }
}
