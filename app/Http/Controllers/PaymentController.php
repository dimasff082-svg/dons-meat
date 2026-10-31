<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Request a Midtrans Snap Token for an unpaid order using its public tracking token.
     */
    public function getSnapToken(string $token): JsonResponse
    {
        $order = Order::where('public_tracking_token', $token)
            ->with(['items', 'payment'])
            ->firstOrFail();

        if ($order->payment_status === PaymentStatus::Paid) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar.',
            ], 400);
        }

        if ($order->order_status === OrderStatus::Expired || $order->payment_status === PaymentStatus::Expired) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu pembayaran untuk pesanan ini telah kedaluwarsa.',
            ], 400);
        }

        if ($order->order_status === OrderStatus::Cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini telah dibatalkan.',
            ], 400);
        }

        try {
            $snapToken = $this->midtransService->getOrCreateSnapToken($order);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sesi pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync and verify order payment status in real-time.
     */
    public function syncStatus(string $token, Request $request): JsonResponse
    {
        $order = Order::where('public_tracking_token', $token)
            ->with(['items', 'payment'])
            ->firstOrFail();

        if ($order->payment_status === PaymentStatus::Paid) {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'order_status' => $order->order_status->value,
                'message' => 'Pesanan ini sudah berstatus lunas.',
            ]);
        }

        $frontendResult = $request->input('result');

        $syncResult = $this->midtransService->syncPaymentStatus($order, is_array($frontendResult) ? $frontendResult : null);

        $order->refresh();

        return response()->json([
            'success' => $syncResult['success'] ?? true,
            'is_paid' => $order->isPaid(),
            'order_status' => $order->order_status->value,
            'message' => $syncResult['message'] ?? 'Status pembayaran diperbarui.',
        ]);
    }
}
