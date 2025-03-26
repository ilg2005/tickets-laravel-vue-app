<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
// use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $tickets = Ticket::with('user:id')->latest('updated_at')->get();
        } else {
            $tickets = Ticket::with('user:id')->where('user_id', $user->id)->latest('updated_at')->get();
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
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
        // try {
        Gate::authorize('create', Ticket::class);
        // } catch (AuthorizationException $e) {
        //     return redirect()->back()->withErrors(['message' => 'You are not authorized to create a ticket.']);
        // }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,in_progress,closed',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $request->user()->tickets()->create($validated);

        return redirect()->route('tickets.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): Response
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['followups' => function ($query) {
            $query->oldest('created_at'); // Load followups in ascending order by created_at
        }, 'followups.user']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            // 'followups' => $ticket->followups,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): Response
    {
        Gate::authorize('update', $ticket);

        $ticket->load(['followups' => function ($query) {
            $query->oldest('created_at'); // Load followups in ascending order by created_at
        }, 'followups.user']);

        return Inertia::render('Tickets/Edit', ['ticket' => $ticket]);
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
}
