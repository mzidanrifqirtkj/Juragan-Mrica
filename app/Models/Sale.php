<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_code',
        'sale_type',
        'transaction_id',
        'buyer_name',
        'buyer_phone',
        'weight_kg',
        'price_per_kg',
        'total_amount',
        'sale_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            // Generate sale code
            if (empty($sale->sale_code)) {
                $sale->sale_code = CodeGeneratorService::generateSaleCode($sale->sale_date);
            }

            // Auto calculate total amount
            $sale->total_amount = $sale->weight_kg * $sale->price_per_kg;
        });

        static::updating(function ($sale) {
            // Auto recalculate total amount on update
            $sale->total_amount = $sale->weight_kg * $sale->price_per_kg;
        });
    }

    /**
     * Get the transactions (purchases) that this sale came from (via pivot)
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'sale_transactions')
            ->withPivot('weight_kg')
            ->withTimestamps();
    }

    /**
     * Get the single transaction (for backward compatibility)
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who created this sale
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the inventory log for this sale
     */
    public function inventoryLog(): HasOne
    {
        return $this->hasOne(InventoryLog::class, 'reference_id')
            ->where('reference_type', 'sale');
    }

    /**
     * Scope for retail sales (pasar)
     */
    public function scopeRetail($query)
    {
        return $query->where('sale_type', 'retail');
    }

    /**
     * Scope for warehouse sales
     */
    public function scopeWarehouse($query)
    {
        return $query->where('sale_type', 'warehouse');
    }

    /**
     * Legacy alias for warehouse sales
     */
    public function scopeBulk($query)
    {
        return $query->where('sale_type', 'warehouse');
    }

    /**
     * Scope for today's sales
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * Scope for this month's sales
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
    }

    /**
     * Scope by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    /**
     * Get badge color for sale type
     */
    public function getSaleTypeBadgeColorAttribute(): string
    {
        return match ($this->sale_type) {
            'warehouse', 'bulk' => 'success',
            'market' => 'primary',
            'retail' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get sale type label
     */
    public function getSaleTypeLabelAttribute(): string
    {
        return match ($this->sale_type) {
            'warehouse', 'bulk' => 'Gudang',
            'market' => 'Pasar',
            'retail' => 'Eceran',
            default => $this->sale_type,
        };
    }
}
