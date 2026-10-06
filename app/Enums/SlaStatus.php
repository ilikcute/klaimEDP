<?php

namespace App\Enums;

enum SlaStatus: string
{
    case ON_TIME = 'ON_TIME';
    case AT_RISK = 'AT_RISK';
    case BREACHED = 'BREACHED';
}
