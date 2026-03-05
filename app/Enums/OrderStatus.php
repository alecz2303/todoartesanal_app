<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment     = 'pending_payment';      // MP esperando pago
    case PendingTransfer    = 'pending_transfer';     // Transfer elegida, esperando
    case TransferSubmitted  = 'transfer_submitted';   // Cliente confirmó/subió comprobante
    case Paid               = 'paid';
    case Failed             = 'failed';
    case Cancelled          = 'cancelled';
}