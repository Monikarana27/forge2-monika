<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::query();

        // Filter by tenant
        $query->where('tenant_id', $user->tenant_id);

        // Role-based filtering
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'agent') {
            $query->where('assigned_agent_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->with(['user', 'assignedAgent', 'replies'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json([
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'user_id' => Auth::id(),
            'tenant_id' => Auth::user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket->load(['user', 'assignedAgent']),
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $ticket = Ticket::where('tenant_id', $user->tenant_id)
                       ->with(['user', 'assignedAgent', 'replies.user'])
                       ->findOrFail($id);

        // Check access based on role
        if ($user->role === 'customer' && $ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'Access denied',
            ], 403);
        }

        if ($user->role === 'agent' && $ticket->assigned_agent_id !== $user->id) {
            return response()->json([
                'message' => 'Access denied',
            ], 403);
        }

        return response()->json([
            'ticket' => $ticket,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::where('tenant_id', $user->tenant_id)->findOrFail($id);

        // Validate and update fields
        $validated = $request->validate([
            'status' => 'in:open,in_progress,resolved',
            'priority' => 'in:low,medium,high',
            'assigned_agent_id' => 'exists:users,id',
        ]);

        // Only admins and agents can update tickets
        if ($user->role === 'customer') {
            return response()->json([
                'message' => 'Access denied',
            ], 403);
        }

        // Update ticket
        $ticket->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket->load(['user', 'assignedAgent']),
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Only admins can delete tickets
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Admin role required.',
            ], 403);
        }

        $ticket = Ticket::where('tenant_id', $user->tenant_id)->findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ]);
    }
}