<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\AskTutorRequest;
use App\Models\AiConversation;
use App\Models\Lesson;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\TutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TutorController extends Controller
{
    public function index(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('view', $lesson);

        $conversation = AiConversation::query()
            ->where('user_id', $request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->latest()
            ->with('messages')
            ->first();

        return response()->json([
            'conversation_id' => $conversation?->id,
            'messages' => $conversation ? $conversation->messages : [],
        ]);
    }

    public function store(AskTutorRequest $request, Lesson $lesson, TutorService $tutor): JsonResponse
    {
        $conversationId = $request->validated('conversation_id');
        $conversation = $conversationId ? AiConversation::find((int) $conversationId) : null;

        try {
            $message = $tutor->ask(
                $request->user(),
                $lesson,
                $request->validated('question'),
                $conversation,
            );
        } catch (AIRequestException $e) {
            report($e);

            return response()->json([
                'message' => 'Không thể kết nối trợ lý AI lúc này. Vui lòng thử lại.',
            ], 503);
        }

        return response()->json([
            'conversation_id' => $message->ai_conversation_id,
            'message' => $message,
        ], 201);
    }
}
