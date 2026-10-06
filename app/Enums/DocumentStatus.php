<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case PENDING = 'PENDING';
    case RECEIVED = 'RECEIVED';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';
    case ARCHIVED = 'ARCHIVED';
}
