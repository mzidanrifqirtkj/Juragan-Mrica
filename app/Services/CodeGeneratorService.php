<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\Transaction;
use App\Models\Sale;
use Carbon\Carbon;

class CodeGeneratorService
{
    /**
     * Generate unique farmer code
     * Format: PET001, PET002, dst
     */
    public static function generateFarmerCode(): string
    {
        $lastFarmer = Farmer::orderBy('id', 'desc')->first();

        if (!$lastFarmer) {
            return 'PET001';
        }

        // Extract number from last code
        $lastNumber = (int) substr($lastFarmer->farmer_code, 3);
        $newNumber = $lastNumber + 1;

        return 'PET' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique transaction code
     * Format: TRX-YYYYMMDD-001
     */
    public static function generateTransactionCode($date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->format('Ymd');
        $prefix = "TRX-{$dateString}-";

        $lastTransaction = Transaction::where('transaction_code', 'like', "{$prefix}%")
            ->orderBy('transaction_code', 'desc')
            ->first();

        if (!$lastTransaction) {
            return $prefix . '001';
        }

        // Extract number from last code
        $lastNumber = (int) substr($lastTransaction->transaction_code, -3);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique sale code
     * Format: SALE-YYYYMMDD-001
     */
    public static function generateSaleCode($date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->format('Ymd');
        $prefix = "SALE-{$dateString}-";

        $lastSale = Sale::where('sale_code', 'like', "{$prefix}%")
            ->orderBy('sale_code', 'desc')
            ->first();

        if (!$lastSale) {
            return $prefix . '001';
        }

        // Extract number from last code
        $lastNumber = (int) substr($lastSale->sale_code, -3);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
