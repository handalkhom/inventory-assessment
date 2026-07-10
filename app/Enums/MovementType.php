<?php

namespace App\Enums;

enum MovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
}
