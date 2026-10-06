<?php

namespace App\Enums;

enum DocumentType: string
{
    case RECEIPT = 'RECEIPT';
    case FUEL_INVOICE = 'FUEL_INVOICE';
    case MEDICAL_LETTER = 'MEDICAL_LETTER';
    case PRESCRIPTION = 'PRESCRIPTION';
    case PAYMENT_PROOF = 'PAYMENT_PROOF';
    case SUPPORTING_DOC = 'SUPPORTING_DOC';
}
