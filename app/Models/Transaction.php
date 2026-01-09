<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Services\CodeGeneratorService;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'farmer_id',
        'weight_kg',
        'price_per_kg',
        'total_amount',
        'payment_proof',
        'payment_method',
        'payment_status',
        'transaction_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Generate transaction code
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = CodeGeneratorService::generateTransactionCode($transaction->transaction_date);
            }

            // Auto calculate total amount
            $transaction->total_amount = $transaction->weight_kg * $transaction->price_per_kg;
        });

        static::updating(function ($transaction) {
            // Auto recalculate total amount on update
            $transaction->total_amount = $transaction->weight_kg * $transaction->price_per_kg;
        });
    }

    /**
     * Get the farmer that owns this transaction
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the inventory log for this transaction
     */
    public function inventoryLog(): HasOne
    {
        return $this->hasOne(InventoryLog::class, 'reference_id')
            ->where('reference_type', 'purchase');
    }

    /**
     * Scope for today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope for this month's transactions
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope by payment method
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [ $startDate, $endDate ]);
    }
}
