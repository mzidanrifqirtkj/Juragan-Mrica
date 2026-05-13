<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'type',
        'weight_kg',
        'current_stock',
        'notes',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'current_stock' => 'decimal:2',
    ];

    /**
     * Get the related transaction (if reference_type is purchase)
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reference_id');
    }

    /**
     * Get the related sale (if reference_type is sale)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'reference_id');
    }

    /**
     * Get the reference model (polymorphic-like)
     */
    public function getReference()
    {
        return match ($this->reference_type) {
            'purchase' => $this->transaction,
            'sale' => $this->sale,
            default => null,
        };
    }

    /**
     * Get reference code (transaction_code or sale_code)
     */
    public function getReferenceCodeAttribute(): ?string
    {
        $reference = $this->getReference();

        return $reference?->transaction_code ?? $reference?->sale_code ?? null;
    }

    /**
     * Scope for stock in (purchases)
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for stock out (sales)
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for purchases
     */
    public function scopePurchases($query)
    {
        return $query->where('reference_type', 'purchase');
    }

    /**
     * Scope for sales
     */
    public function scopeSales($query)
    {
        return $query->where('reference_type', 'sale');
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            default => $this->type,
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'in' => 'success',
            'out' => 'danger',
            default => 'gray',
        };
    }
}
