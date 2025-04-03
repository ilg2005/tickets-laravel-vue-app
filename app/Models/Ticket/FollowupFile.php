<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class FollowupFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'followup_id',
        'user_id',
        'original_filename',
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * Get the followup that the file belongs to.
     */
    public function followup(): BelongsTo
    {
        return $this->belongsTo(Followup::class);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
