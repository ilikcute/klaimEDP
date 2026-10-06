<?php

namespace App\Enums;

enum ClaimType: string
{
    case BBM = 'BBM';
    case TOL = 'TOL';
    case HEALTH = 'HEALTH';

    public function label(): string
    {
        return match ($this) {
            self::BBM => 'Bahan Bakar Minyak (BBM)',
            self::TOL => 'Tol & Parkir',
            self::HEALTH => 'Kesehatan (Rawat Jalan/Obat)',
        };
    }
}
