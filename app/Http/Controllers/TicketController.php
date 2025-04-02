<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\FileService;
use App\Services\TicketFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /**
     * Сервис для работы с файлами
     *
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * Сервис для фильтрации тикетов
     *
     * @var TicketFilterService
     */
    protected TicketFilterService $ticketFilterService;

    /**
     * Конструктор контроллера с внедрением зависимостей
     *
     * @param FileService $fileService
     * @param TicketFilterService $ticketFilterService
     */
    public function __construct(FileService $fileService, TicketFilterService $ticketFilterService)
    {
        $this->fileService = $fileService;
        $this->ticketFilterService = $ticketFilterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Получаем отфильтрованные и отсортированные тикеты с использованием сервиса
        $data = $this->ticketFilterService->getFilteredTickets($request);
        
        return Inertia::render('Tickets/Index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Tickets/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Ticket::class);

        // Валидируем основные поля (без файлов)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,in_progress,closed',
            'priority' => 'required|string|in:low,medium,high',
        ]);
        
        $ticket = $request->user()->tickets()->create($validated);
        
        // Используем единый метод для валидации и загрузки файлов
        $this->fileService->validateAndUpload($ticket, $request);
        
        return redirect()->route('tickets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): Response
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['followups' => function ($query) {
            $query->oldest('created_at');
        }, 'followups.user', 'followups.files', 'files', 'files.user']);

        // Получаем все файлы (тикета и follow-up)
        $allFiles = $ticket->getAllFiles();

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'allFiles' => $allFiles,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): Response
    {
        Gate::authorize('update', $ticket);

        $ticket->load(['followups' => function ($query) {
            $query->oldest('created_at');
        }, 'followups.user', 'followups.files', 'files', 'files.user']);

        // Получаем все файлы (тикета и follow-up)
        $allFiles = $ticket->getAllFiles();

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'allFiles' => $allFiles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
        ]);

        $ticket->update($validated);

        $ticket->load(['followups' => function ($query) {
            $query->latest(); // Load followups in descending order by created_at
        }, 'followups.user']);

        return redirect()->route('tickets.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index');
    }
    
    /**
     * Скачивание файла, прикрепленного к тикету
     * 
     * @param int $fileId ID файла для скачивания
     * @return StreamedResponse|RedirectResponse
     */
    public function downloadFile(int $fileId): StreamedResponse|RedirectResponse
    {
        return $this->fileService->downloadFileWithAuthorization('ticket', $fileId);
    }
}
