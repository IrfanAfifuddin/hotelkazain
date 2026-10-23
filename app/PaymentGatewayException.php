<?php

namespace App;

use RuntimeException;

class PaymentGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $transactionMayExist = false,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
