<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MercadoPago = 'mp';
    case Transfer    = 'transfer';
}