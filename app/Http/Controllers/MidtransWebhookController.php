<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Handle incoming Midtrans HTTP(S) notification / webhook.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $result = $this->midtransService->processNotification($payload);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status_code'] ?? 200);
    }
}
