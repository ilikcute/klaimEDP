<?php

namespace App\Enums;

enum ClaimState: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case EDP_REVIEW = 'EDP_REVIEW';
    case SUPERVISOR_VERIFICATION = 'SUPERVISOR_VERIFICATION';
    case MANAGER_VERIFICATION = 'MANAGER_VERIFICATION';
    case FINANCE_VERIFICATION = 'FINANCE_VERIFICATION';
    case MANAGEMENT_APPROVAL = 'MANAGEMENT_APPROVAL';
    case APPROVED = 'APPROVED';
    case PAYMENT_PROCESS = 'PAYMENT_PROCESS';
    case PAID = 'PAID';
    case NEED_REVISION = 'NEED_REVISION';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Diajukan (Submitted)',
            self::EDP_REVIEW => 'Review EDP Adm',
            self::SUPERVISOR_VERIFICATION => 'Verifikasi SPV',
            self::MANAGER_VERIFICATION => 'Verifikasi Manager',
            self::FINANCE_VERIFICATION => 'Verifikasi Finance',
            self::MANAGEMENT_APPROVAL => 'Approval Management (DBM/BM)',
            self::APPROVED => 'Disetujui (Approved)',
            self::PAYMENT_PROCESS => 'Proses Pembayaran',
            self::PAID => 'Selesai Dibayar (Paid)',
            self::NEED_REVISION => 'Perlu Revisi',
            self::REJECTED => 'Ditolak (Rejected)',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::NEED_REVISION]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::PAID, self::REJECTED, self::CANCELLED]);
    }
}
