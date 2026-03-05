<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Pickup   = 'pickup';
    case Shipping = 'shipping';
}