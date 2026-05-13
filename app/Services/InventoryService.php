<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Transaction;

class InventoryService
{
    /**
     * Get current stock from the latest inventory log
     */
    public static function getCurrentStock(): float
    {
        $lastLog = InventoryLog::latest()->first();

        return $lastLog ? (float) $lastLog->current_stock : 0;
    }

    /**
     * Add stock (from purchase/transaction)
     */
    public static function addStock(Transaction $transaction, ?string $notes = null): InventoryLog
    {
        $currentStock = self::getCurrentStock();
        $newStock = $currentStock + $transaction->weight_kg;

        return InventoryLog::create([
            'reference_type' => 'purchase',
            'reference_id' => $transaction->id,
            'type' => 'in',
            'weight_kg' => $transaction->weight_kg,
            'current_stock' => $newStock,
            'notes' => $notes ?? "Setoran dari {$transaction->farmer->name}",
        ]);
    }

    /**
     * Reduce stock (from sale)
     */
    public static function reduceStock(Sale $sale, ?string $notes = null): InventoryLog
    {
        $currentStock = self::getCurrentStock();
        $newStock = $currentStock - $sale->weight_kg;

        // Prevent negative stock
        if ($newStock < 0) {
            throw new \Exception('Stok tidak mencukupi. Stok saat ini: '.$currentStock.' kg');
        }

        return InventoryLog::create([
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'type' => 'out',
            'weight_kg' => $sale->weight_kg,
            'current_stock' => $newStock,
            'notes' => $notes ?? "Penjualan ke {$sale->buyer_name}",
        ]);
    }

    /**
     * Check if stock is near target (1 ton)
     */
    public static function isNearTarget(): bool
    {
        $currentStock = self::getCurrentStock();
        $alertThreshold = Setting::get('stock_alert_threshold', 900);
        $targetStock = Setting::get('target_stock', 1000);

        return $currentStock >= $alertThreshold && $currentStock < $targetStock;
    }

    /**
     * Check if stock has reached target
     */
    public static function hasReachedTarget(): bool
    {
        $currentStock = self::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);

        return $currentStock >= $targetStock;
    }

    /**
     * Check if stock is low
     */
    public static function isLowStock(): bool
    {
        $currentStock = self::getCurrentStock();
        $lowStockWarning = Setting::get('low_stock_warning', 100);

        return $currentStock < $lowStockWarning;
    }

    /**
     * Get stock percentage towards target
     */
    public static function getStockPercentage(): float
    {
        $currentStock = self::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);

        return min(100, ($currentStock / $targetStock) * 100);
    }

    /**
     * Get stock status with color
     */
    public static function getStockStatus(): array
    {
        $currentStock = self::getCurrentStock();
        $lowStockWarning = Setting::get('low_stock_warning', 100);
        $alertThreshold = Setting::get('stock_alert_threshold', 900);
        $targetStock = Setting::get('target_stock', 1000);

        if ($currentStock < $lowStockWarning) {
            return [
                'status' => 'low',
                'color' => 'danger',
                'message' => 'Stok rendah! Segera terima setoran.',
                'icon' => 'heroicon-o-exclamation-triangle',
            ];
        } elseif ($currentStock >= $targetStock) {
            return [
                'status' => 'ready',
                'color' => 'success',
                'message' => 'Stok sudah mencapai target! Siap dijual ke pengepul.',
                'icon' => 'heroicon-o-check-circle',
            ];
        } elseif ($currentStock >= $alertThreshold) {
            return [
                'status' => 'near_target',
                'color' => 'warning',
                'message' => 'Hampir mencapai 1 ton! Persiapkan penjualan bulk.',
                'icon' => 'heroicon-o-bell-alert',
            ];
        } else {
            return [
                'status' => 'normal',
                'color' => 'info',
                'message' => 'Stok normal.',
                'icon' => 'heroicon-o-cube',
            ];
        }
    }

    /**
     * Validate if sale weight is available
     */
    public static function validateSaleWeight(float $weight): bool
    {
        return self::getCurrentStock() >= $weight;
    }

    /**
     * Get estimated days to reach target based on recent transactions
     */
    public static function getEstimatedDaysToTarget(): ?int
    {
        $currentStock = self::getCurrentStock();
        $targetStock = Setting::get('target_stock', 1000);

        if ($currentStock >= $targetStock) {
            return 0;
        }

        // Calculate average daily stock in from last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $totalStockIn = InventoryLog::stockIn()
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('weight_kg');

        $daysOfData = min(30, now()->diffInDays($thirtyDaysAgo));

        if ($daysOfData == 0 || $totalStockIn == 0) {
            return null;
        }

        $averageDailyIn = $totalStockIn / $daysOfData;
        $remaining = $targetStock - $currentStock;

        return (int) ceil($remaining / $averageDailyIn);
    }
}
