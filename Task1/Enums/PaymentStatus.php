<?php

namespace Task1\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case CashOnDelivery = 'cash_on_delivery';
    case AwaitingInvoice = 'awaiting_invoice';
}
