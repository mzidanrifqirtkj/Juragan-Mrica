<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\InventoryService;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Auto create inventory log when transaction is created
        InventoryService::addStock($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // If weight changed, we need to adjust inventory
        if ($transaction->wasChanged('weight_kg')) {
            $inventoryLog = $transaction->inventoryLog;

            if ($inventoryLog) {
                $oldWeight = $inventoryLog->weight_kg;
                $newWeight = $transaction->weight_kg;
                $difference = $newWeight - $oldWeight;

                // Get latest stock and recalculate
                $latestStock = InventoryService::getCurrentStock();
                $newStock = $latestStock + $difference;

                // Update the inventory log
                $inventoryLog->update([
                    'weight_kg' => $newWeight,
                    'current_stock' => $newStock,
                    'notes' => "Diupdate: Setoran dari {$transaction->farmer->name}",
                ]);
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        // Delete associated inventory log
        $transaction->inventoryLog?->delete();

        // Note: In production, you might want to recalculate all subsequent logs
        // For simplicity, we just delete the log here
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        // Re-create inventory log if transaction is restored (soft delete)
        InventoryService::addStock($transaction, 'Dipulihkan: Setoran dari '.$transaction->farmer->name);
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        // Delete associated inventory log permanently
        $transaction->inventoryLog?->forceDelete();
    }
}
