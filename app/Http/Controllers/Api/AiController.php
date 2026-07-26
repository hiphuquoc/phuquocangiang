<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AiController extends Controller
{
    public function __construct(private readonly AiGatewayService $aiGatewayService)
    {
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->aiGatewayService->health(),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:system,user,assistant'],
            'messages.*.content' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->aiGatewayService->chat(
                $request->input('messages', []),
                $request->only(['model'])
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $result['content'],
                    'model' => $result['model'],
                    'usage' => $result['usage'],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected AI error.',
            ], 500);
        }
    }

    public function translate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
            'target_language' => ['required', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->aiGatewayService->translate(
                (string) $request->input('text'),
                (string) $request->input('target_language'),
                $request->only(['model'])
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'translated_text' => $result['content'],
                    'model' => $result['model'],
                    'usage' => $result['usage'],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected AI error.',
            ], 500);
        }
    }

    public function summarize(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->aiGatewayService->summarize(
                (string) $request->input('text'),
                $request->only(['model'])
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $result['content'],
                    'model' => $result['model'],
                    'usage' => $result['usage'],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected AI error.',
            ], 500);
        }
    }
}
