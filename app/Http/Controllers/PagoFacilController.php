<?php

namespace App\Http\Controllers;

use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PagoFacilController extends Controller
{
    protected PagoFacilService $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Genera un QR para el pago
     */
    public function generateQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|integer',
            'client_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'items' => 'required|array|min:1',
        ]);

        // Generar un número de pago único
        $paymentNumber = 'ORD-' . time() . '-' . rand(1000, 9999);

        $result = $this->pagoFacilService->generateQr([
            'client_id' => $validated['client_id'] ?? $request->user()->id,
            'client_name' => $validated['client_name'],
            'amount' => $validated['amount'],
            'payment_number' => $paymentNumber,
        ]);

        if (isset($result['error']) && $result['error'] === 0) {
            // Guardar en caché los datos de la transacción para usarlos después
            $cacheKey = 'pago_qr_' . $paymentNumber;
            Cache::put($cacheKey, [
                'transaction_id' => $result['values']['transactionId'] ?? null,
                'payment_number' => $paymentNumber,
                'client_id' => $validated['client_id'] ?? $request->user()->id,
                'client_name' => $validated['client_name'],
                'amount' => $validated['amount'],
                'items' => $validated['items'],
                'status' => 'pending',
            ], now()->addHours(1));

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_base64' => $result['values']['qrBase64'] ?? null,
                    'transaction_id' => $result['values']['transactionId'] ?? null,
                    'payment_number' => $paymentNumber,
                    'expiration_date' => $result['values']['expirationDate'] ?? null,
                    'checkout_url' => $result['values']['checkoutUrl'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Error al generar QR',
        ], 400);
    }

    /**
     * Consulta el estado de una transacción
     */
    public function queryTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $result = $this->pagoFacilService->queryTransaction($validated['transaction_id']);

        if (isset($result['error']) && $result['error'] === 0) {
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_status' => $result['values']['paymentStatus'] ?? null,
                    'amount' => $result['values']['amount'] ?? null,
                    'payment_date' => $result['values']['paymentDate'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Error al consultar transacción',
        ], 400);
    }

    /**
     * Callback de PagoFácil - recibe notificaciones de pago
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('PagoFacil Callback Received', ['data' => $request->all()]);

        $pedidoId = $request->input('PedidoID');
        $fecha = $request->input('Fecha');
        $hora = $request->input('Hora');
        $metodoPago = $request->input('MetodoPago');
        $estado = $request->input('Estado');

        // Buscar la transacción en caché
        $cacheKey = 'pago_qr_' . $pedidoId;
        $transactionData = Cache::get($cacheKey);

        if ($transactionData) {
            // Actualizar el estado en caché
            $transactionData['status'] = $estado;
            $transactionData['payment_date'] = $fecha;
            $transactionData['payment_time'] = $hora;
            $transactionData['payment_method'] = $metodoPago;
            Cache::put($cacheKey, $transactionData, now()->addHours(1));

            // También guardar el estado del callback para que el frontend pueda consultarlo
            Cache::put('callback_status_' . $pedidoId, [
                'status' => $estado,
                'fecha' => $fecha,
                'hora' => $hora,
                'metodo_pago' => $metodoPago,
                'received_at' => now()->toDateTimeString(),
            ], now()->addHours(1));
        }

        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Notificación recibida correctamente',
            'values' => true,
        ]);
    }

    /**
     * Obtiene el estado del callback para un pedido
     */
    public function getCallbackStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_number' => 'required|string',
        ]);

        $cacheKey = 'callback_status_' . $validated['payment_number'];
        $callbackStatus = Cache::get($cacheKey);

        if ($callbackStatus) {
            return response()->json([
                'success' => true,
                'data' => $callbackStatus,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se ha recibido notificación de pago',
        ]);
    }

    /**
     * Obtiene los datos de una transacción guardada
     */
    public function getTransactionData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_number' => 'required|string',
        ]);

        $cacheKey = 'pago_qr_' . $validated['payment_number'];
        $transactionData = Cache::get($cacheKey);

        if ($transactionData) {
            return response()->json([
                'success' => true,
                'data' => $transactionData,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transacción no encontrada',
        ], 404);
    }
}
