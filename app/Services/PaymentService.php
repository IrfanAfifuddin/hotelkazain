<?php

namespace App\Services;

class PaymentService
{
    public function createPayment(array $payload): array
    {
        return [
            'status' => 'pending',
            'payload' => $payload,
        ];
    }
}
