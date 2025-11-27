<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    protected string $baseUrl;
    protected string $token;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.url_base');
        $this->token = config('services.pagofacil.token');
        $this->callbackUrl = config('services.pagofacil.callback_url');
    }

    /**
     * Genera un QR para pago
     */
    public function generateQr(array $data): array
    {
        $payload = [
            'paymentMethod' => 4,
            'clientName' => $data['client_name'] ?? 'Cliente',
            'documentType' => 1,
            'documentId' => '12447788',
            'phoneNumber' => '62004638',
            'email' => 'joseluis.universidad2020@gmail.com',
            'paymentNumber' => $data['payment_number'],
            //'amount' => $data['amount'],
            'amount' => 0.1, // Modo prueba PagoFácil
            'currency' => 2,
            'clientCode' => (string) ($data['client_id'] ?? '0001'),
            'callbackUrl' => $this->callbackUrl,
            'orderDetail' => [
                [
                    'serial' => 1,
                    'product' => 'Detalle de Pago',
                    'quantity' => 1,
                    'price' => $data['amount'],
                    'discount' => 0,
                    'total' => $data['amount'],
                ]
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/generate-qr', $payload);

            $result = $response->json();

            Log::info('PagoFacil Generate QR Response', ['response' => $result]);

            return $result;
        } catch (\Exception $e) {
            Log::error('PagoFacil Generate QR Error', ['error' => $e->getMessage()]);
            return [
                'error' => 1,
                'message' => 'Error al conectar con PagoFácil: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta el estado de una transacción
     */
    public function queryTransaction(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/query-transaction', [
                'pagofacilTransactionId' => $transactionId,
            ]);

            $result = $response->json();

            Log::info('PagoFacil Query Transaction Response', ['response' => $result]);

            return $result;
        } catch (\Exception $e) {
            Log::error('PagoFacil Query Transaction Error', ['error' => $e->getMessage()]);
            return [
                'error' => 1,
                'message' => 'Error al consultar transacción: ' . $e->getMessage(),
            ];
        }
    }
}
