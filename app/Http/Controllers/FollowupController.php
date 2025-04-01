<?php

namespace App\Http\Controllers;

use App\Models\Followup;
use App\Models\FollowupFile;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FollowupController extends Controller
{
    public function index(Ticket $ticket)
    {
        $followups = $ticket->followups()->with('files')->get();
        return Inertia::render('Followups/Index', [
            'followups' => $followups,
            'ticket' => $ticket,
        ]);
    }

    public function show(Followup $followup)
    {
        $followup->load('files');
        return Inertia::render('Followups/Show', [
            'followup' => $followup,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Followup::class);

        if ($request->type === 'solution') {
            if (!Gate::allows('createSolution', Followup::class)) {
                return back()->withErrors(['Unauthorized to create solution follow-ups.']);
            }

            $existingSolution = Followup::where('ticket_id', $request->ticket_id)
                                        ->where('type', 'solution')
                                        ->first();

            if ($existingSolution) {
                return back()->withErrors(['A solution follow-up already exists for this ticket.']);
            }
        }

        if ($request->type === 'comment') {
            if (!Gate::allows('createComment', Followup::class)) {
                return redirect()->back()->with('error', 'Unauthorized to create comment follow-ups.');
            }
        }

        $request->validate([
            'content' => 'required|string',
            'type' => 'required|string|in:comment,solution',
            'ticket_id' => 'required|exists:tickets,id',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,zip,rar,txt|max:10240',
        ]);

        $followup = Followup::create([
            'content' => $request->content,
            'type' => $request->type,
            'ticket_id' => $request->ticket_id,
            'user_id' => Auth::id(),
        ]);

        // Обрабатываем загруженные файлы
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('followup_attachments/' . $followup->id, 'local');

                $followup->files()->create([
                    'user_id' => Auth::id(),
                    'original_filename' => $file->getClientOriginalName(),
                    'filename' => basename($path),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Followup created successfully.');
    }

    public function update(Request $request, Followup $followup)
    {
        if (!Gate::allows('update', $followup)) {
            return back()->withErrors(['Unauthorized to update follow-up.']);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $followup->update([
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Followup updated successfully.');
    }

    public function destroy(Followup $followup)
    {
        if (!Gate::allows('delete', $followup)) {
            return back()->withErrors(['Unauthorized to delete follow-up.']);
        }

        $followup->delete();

        return redirect()->back()->with('message', 'Followup deleted successfully.');
    }
    
    /**
     * Handle file download request.
     */
    public function downloadFile(Followup $followup, FollowupFile $followupFile)
    {
        // Проверяем, действительно ли файл принадлежит этому followup
        if ($followupFile->followup_id !== $followup->id) {
            abort(404);
        }

        // Авторизуем пользователя
        Gate::authorize('view', $followup->ticket);

        // Проверяем, существует ли файл физически
        if (!Storage::disk('local')->exists($followupFile->path)) {
            return redirect()->back()->withErrors(['file_error' => 'File not found on server.']);
        }

        // Отдаем файл для скачивания
        return Storage::disk('local')->download($followupFile->path, $followupFile->original_filename);
    }
}
