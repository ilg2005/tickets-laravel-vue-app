<?php

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket\Followup;
use App\Models\User;
use App\Notifications\Ticket\NewFollowupNotification;
use App\Services\Ticket\FileService;
use App\Constants\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class FollowupController extends Controller
{
    /**
     * Service for file operations
     *
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * Controller constructor with FileService dependency injection
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function store(Request $request)
    {
        // Проверка базового разрешения на создание followup
        Gate::authorize('create', Followup::class);

        // Проверка разрешения в зависимости от типа followup
        if ($request->type === Permissions::FOLLOWUP_TYPE_SOLUTION) {
            Gate::authorize('createSolution', Followup::class);

            // Проверка на существование solution для данного тикета
            $existingSolution = Followup::where('ticket_id', $request->ticket_id)
                                        ->where('type', Permissions::FOLLOWUP_TYPE_SOLUTION)
                                        ->first();

            if ($existingSolution) {
                return back()->withErrors(['A solution follow-up already exists for this ticket.']);
            }
        } elseif ($request->type === Permissions::FOLLOWUP_TYPE_COMMENT) {
            Gate::authorize('createComment', Followup::class);
        }

        // Валидируем основные поля (без файлов)
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|string|in:' . Permissions::FOLLOWUP_TYPE_COMMENT . ',' . Permissions::FOLLOWUP_TYPE_SOLUTION,
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        // Временно отключаем события модели для ручной обработки уведомлений
        Followup::withoutEvents(function () use ($request) {
            $followup = Followup::create([
                'content' => $request->content,
                'type' => $request->type,
                'ticket_id' => $request->ticket_id,
                'user_id' => Auth::id(),
            ]);

            // Используем FileService для валидации и загрузки файлов
            $this->fileService->validateAndUpload($followup, $request);

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
                $query->where('name', Permissions::ROLE_ADMIN);
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
        Gate::authorize('update', $followup);

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
        Gate::authorize('delete', $followup);

        $followup->delete();

        return redirect()->back()->with('message', 'Followup deleted successfully.');
    }    
    
    /**
     * Скачивание файла, прикрепленного к ответу
     * 
     * @param int $fileId ID файла для скачивания
     * @return StreamedResponse|RedirectResponse
     */
    public function downloadFile(int $fileId): StreamedResponse|RedirectResponse
    {
        return $this->fileService->downloadFileWithAuthorization('followup', $fileId);
    }
}
