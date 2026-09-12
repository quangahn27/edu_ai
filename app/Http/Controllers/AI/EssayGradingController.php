<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateEssayGradingRequest;
use App\Models\QuizAnswer;
use App\Services\AI\EssayGradingService;
use App\Services\AI\Exceptions\AIRequestException;
use Illuminate\Http\JsonResponse;

class EssayGradingController extends Controller
{
    public function generate(GenerateEssayGradingRequest $request, QuizAnswer $answer, EssayGradingService $service): JsonResponse
    {
        try {
            $grading = $service->grade($request->user(), $answer);
        } catch (AIRequestException $e) {
            report($e);

            return response()->json([
                'message' => 'Không thể kết nối trợ lý AI lúc này. Vui lòng thử lại.',
            ], 503);
        }

        return response()->json(['grading' => $grading]);
    }
}
