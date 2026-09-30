<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PRODUCT_MANAGER = 'product-manager';
    case HRD_MANAGER = 'hrd-manager';
    case OA_OC_MANAGER = 'oa-oc-manager';
    case KARYAWAN = 'karyawan';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::PRODUCT_MANAGER => 'Manajer Produk',
            self::HRD_MANAGER => 'Manajer HRD',
            self::OA_OC_MANAGER => 'Manajer OA/OC',
            self::KARYAWAN => 'Karyawan',
        };
    }
}
