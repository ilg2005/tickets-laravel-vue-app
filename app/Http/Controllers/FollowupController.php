<?php

namespace App\Http\Controllers;

use App\Models\Followup;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewFollowupNotification;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class FollowupController extends Controller
{
    /**
     * Сервис для работы с файлами
     *
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * Конструктор контроллера с внедрением зависимости FileService
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

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

        // Отключаем наблюдатель временно
        Followup::withoutEvents(function () use ($request) {
            $followup = Followup::create([
                'content' => $request->content,
                'type' => $request->type,
                'ticket_id' => $request->ticket_id,
                'user_id' => Auth::id(),
            ]);

            // Используем FileService для загрузки файлов
            if ($request->hasFile('files')) {
                $this->fileService->uploadFiles($followup, $request->file('files'));
            }

            // Перезагружаем модель с файлами
            $followup->load('files', 'ticket', 'user');
            
            // Теперь вручную вызываем обработчик уведомлений
            $ticket = $followup->ticket;
            $ticketOwner = $ticket->user;
            $followupCreator = $followup->user;

            if ($ticketOwner->id !== $followupCreator->id) {
                $ticketOwner->notify(new NewFollowupNotification($followup));
            }

            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->where('id', '!=', $followupCreator->id)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewFollowupNotification($followup));
            }
            
            return $followup;
        });

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
    
}
