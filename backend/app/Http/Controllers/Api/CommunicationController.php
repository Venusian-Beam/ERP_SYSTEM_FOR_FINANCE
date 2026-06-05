<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function messages(Request $request): JsonResponse
    {
        $query = ChatMessage::query()
            ->with('user:id,name')
            ->where('tenant_id', TenantContext::requireId());

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        return response()->json(
            $query->latest()->paginate(50)->withQueryString()
        );
    }

    public function storeMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'message' => 'required|string',
            'message_type' => 'nullable|string|in:text,file,image',
            'attachment_url' => 'nullable|string|max:500',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();
        $validated['user_id'] = $request->user()->id;

        $message = ChatMessage::create($validated);
        $message->load('user:id,name');

        return response()->json($message, 201);
    }

    public function markAsRead(Request $request, ChatMessage $chatMessage): JsonResponse
    {
        $chatMessage->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }
}
