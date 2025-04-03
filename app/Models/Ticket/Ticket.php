<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;


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

    /**
     * Get all files associated with the ticket (both direct files and followup files).
     */
    public function getAllFiles()
    {
        // Получаем файлы тикета
        $ticketFiles = $this->files()->with('user')->get();
        
        // Получаем файлы follow-up
        $followupFiles = FollowupFile::whereHas('followup', function ($query) {
            $query->where('ticket_id', $this->id);
        })->with(['followup', 'followup.user', 'user'])->get();
        
        // Преобразуем followupFiles для совместимости с интерфейсом
        $formattedFollowupFiles = $followupFiles->map(function ($file) {
            $file->is_followup = true;
            $file->followup_id = $file->followup->id;
            $file->followup_created_at = $file->followup->created_at;
            return $file;
        });
        
        // Добавляем флаг для файлов тикета
        $ticketFiles->each(function ($file) {
            $file->is_followup = false;
        });
        
        // Объединяем коллекции и сортируем по дате создания
        return $ticketFiles->concat($formattedFollowupFiles)
            ->sortByDesc('created_at')
            ->values();
    }
}
