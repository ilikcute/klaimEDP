<?php

namespace App\Enums;

enum ApprovalRule: string
{
    case ANY_OF = 'ANY_OF';
    case ALL_OF = 'ALL_OF';
    case N_OF = 'N_OF';
}
