<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description', // Add other attributes as needed
        'status',
        'priority',
        // Add any other attributes that should be mass assignable
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    /**
     * Get the files for the ticket.
     */
    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class);
    }
}
