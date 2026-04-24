<?php

namespace Task1\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case Cash = 'cash';
    case Invoice = 'invoice';

    public function toStatus(): PaymentStatus
    {
        return match ($this) {
            self::Card => PaymentStatus::Paid,
            self::Cash => PaymentStatus::CashOnDelivery,
            self::Invoice => PaymentStatus::AwaitingInvoice
        };
    }
}
