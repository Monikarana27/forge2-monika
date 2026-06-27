<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    public function store(Request $request, $ticketId)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $user = Auth::user();
        $ticket = Ticket::where('tenant_id', $user->tenant_id)->findOrFail($ticketId);

        // Check access
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

        $reply = Reply::create([
            'body' => $validated['body'],
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);

        return response()->json([
            'message' => 'Reply created successfully',
            'reply' => $reply->load(['user']),
        ], 201);
    }
}