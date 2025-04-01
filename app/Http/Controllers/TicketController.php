<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketFile;
// use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Base query depending on user role
        $query = Ticket::with('user');  // Загружаем полные данные пользователя
        
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }
        
        // Apply filters
        if ($request->has('filters')) {
            $filters = $request->filters;
            
            if (!empty($filters['id'])) {
                $query->where('id', 'like', $filters['id'] . '%');
            }
            
            if (!empty($filters['title'])) {
                $query->where('title', 'like', '%' . $filters['title'] . '%');
            }
            
            if (!empty($filters['description'])) {
                $query->where('description', 'like', '%' . $filters['description'] . '%');
            }
            
            if (isset($filters['status']) && $filters['status'] !== null && $filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['priority']) && $filters['priority'] !== null && $filters['priority'] !== '') {
                $query->where('priority', $filters['priority']);
            }
            
            // Фильтрация по имени пользователя (для админов)
            if ($user->hasRole('admin') && !empty($filters['user_name'])) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['user_name'] . '%');
                });
            }
        }
        
        // Apply sorting
        $sortField = $request->input('sort.field', 'updated_at');
        $sortOrder = $request->input('sort.order', 'desc');
        
        if ($sortField === 'user_name') {
            // Сортировка по имени пользователя через join
            $query->join('users', 'tickets.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('tickets.*');
        } else {
            $query->orderBy($sortField, $sortOrder);
        }
        
        // Get results
        $tickets = $query->get();
        
        // Проверяем, является ли пользователь администратором
        $isAdmin = $user->hasRole('admin');
        
        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->input('filters', []),
            'sort' => [
                'field' => $sortField,
                'order' => $sortOrder
            ],
            'isAdmin' => $isAdmin
        ]);
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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,in_progress,closed',
            'priority' => 'required|string|in:low,medium,high',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,zip,rar,txt|max:10240',
        ]);

        $ticketData = collect($validated)->except('files')->toArray();
        $ticket = $request->user()->tickets()->create($ticketData);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('ticket_attachments/' . $ticket->id, 'local');

                $ticket->files()->create([
                    'user_id' => $request->user()->id,
                    'original_filename' => $file->getClientOriginalName(),
                    'filename' => basename($path),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

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

        // Eager load followups with user relationship
        // $ticket->load('followups.user');

        // // Eager load followups with user relationship
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
     * Handle file download request.
     *
     * @param Ticket $ticket The ticket instance from Route Model Binding.
     * @param TicketFile $ticketFile The ticket file instance from Route Model Binding.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadFile(Ticket $ticket, TicketFile $ticketFile): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        // 1. Проверяем, действительно ли файл принадлежит этому тикету
        if ($ticketFile->ticket_id !== $ticket->id) {
            abort(404); // Или другое сообщение об ошибке
        }

        // 2. Авторизуем пользователя (может ли он видеть этот тикет?)
        Gate::authorize('view', $ticket);

        // 3. Проверяем, существует ли файл физически
        if (!Storage::disk('local')->exists($ticketFile->path)) {
            // Можно добавить логирование или flash-сообщение
             return redirect()->back()->withErrors(['file_error' => 'File not found on server.']);
            // abort(404, 'File not found.');
        }

        // 4. Отдаем файл для скачивания
        // Storage::download(путь_к_файлу, имя_файла_для_пользователя)
        return Storage::disk('local')->download($ticketFile->path, $ticketFile->original_filename);
    }
}
