<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\InventoryService;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // Auto create inventory log when sale is created
        InventoryService::reduceStock($sale);
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // If weight changed, we need to adjust inventory
        if ($sale->wasChanged('weight_kg')) {
            $inventoryLog = $sale->inventoryLog;

            if ($inventoryLog) {
                $oldWeight = $inventoryLog->weight_kg;
                $newWeight = $sale->weight_kg;
                $difference = $newWeight - $oldWeight;

                // Get latest stock and recalculate
                $latestStock = InventoryService::getCurrentStock();
                $newStock = $latestStock - $difference;

                // Validate stock won't go negative
                if ($newStock < 0) {
                    throw new \Exception('Perubahan berat akan membuat stok negatif. Stok saat ini: ' . $latestStock . ' kg');
                }

                // Update the inventory log
                $inventoryLog->update([
                    'weight_kg' => $newWeight,
                    'current_stock' => $newStock,
                    'notes' => "Diupdate: Penjualan ke {$sale->buyer_name}",
                ]);
            }
        }
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        // Delete associated inventory log
        $sale->inventoryLog?->delete();

        // Note: In production, you might want to recalculate all subsequent logs
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        // Re-create inventory log if sale is restored (soft delete)
        InventoryService::reduceStock($sale, 'Dipulihkan: Penjualan ke ' . $sale->buyer_name);
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        // Delete associated inventory log permanently
        $sale->inventoryLog?->forceDelete();
    }
}
