<?php

namespace App\Http\Controllers;

use App\Models\TicketFile;
use App\Models\FollowupFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Handle file download request.
     */
    public function download($file_type, $file_id)
    {
        if ($file_type === 'ticket') {
            return $this->downloadTicketFile($file_id);
        } elseif ($file_type === 'followup') {
            return $this->downloadFollowupFile($file_id);
        }
        
        abort(404);
    }
    
    private function downloadTicketFile($file_id)
    {
        $ticketFile = TicketFile::findOrFail($file_id);
        
        // Авторизуем пользователя
        Gate::authorize('view', $ticketFile->ticket);
        
        // Проверяем, существует ли файл физически
        if (!Storage::disk('local')->exists($ticketFile->path)) {
            return redirect()->back()->withErrors(['file_error' => 'File not found on server.']);
        }
        
        // Отдаем файл для скачивания
        return Storage::disk('local')->download($ticketFile->path, $ticketFile->original_filename);
    }
    
    private function downloadFollowupFile($file_id)
    {
        $followupFile = FollowupFile::findOrFail($file_id);
        
        // Авторизуем пользователя
        Gate::authorize('view', $followupFile->followup->ticket);
        
        // Проверяем, существует ли файл физически
        if (!Storage::disk('local')->exists($followupFile->path)) {
            return redirect()->back()->withErrors(['file_error' => 'File not found on server.']);
        }
        
        // Отдаем файл для скачивания
        return Storage::disk('local')->download($followupFile->path, $followupFile->original_filename);
    }
} 