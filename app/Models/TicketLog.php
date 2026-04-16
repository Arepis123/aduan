<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketLog extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'action',
        'description',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSystem(): bool
    {
        return $this->type === 'system';
    }

    public function isManual(): bool
    {
        return $this->type === 'manual';
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
