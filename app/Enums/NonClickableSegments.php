<?php

namespace App\Enums;

enum NonClickableSegments: string
{

    // phpcs:disable
    case APPLIED_CREDITS = 'applied-credits';
    case CREDIT_INVOICES = 'credited-invoices';
    case VIEW_TRANSACTION = 'view-transaction';
    case APPLY_TO_INVOICE = 'apply-to-invoice';
    case CONVERT_INVOICE = 'convert-invoice';
    case PROJECT_NOTES = 'project-notes';
    case CLIENT_CONTACT = 'client-contacts';
    // phpcs:enable

    public static function getValues()
    {
        return array_map(function($enum) {
            return $enum->value;
        }, self::cases());
    }

}
