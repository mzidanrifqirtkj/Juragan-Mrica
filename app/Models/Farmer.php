<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\CodeGeneratorService;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_code',
        'name',
        'address',
        'phone',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($farmer) {
            if (empty($farmer->farmer_code)) {
                $farmer->farmer_code = CodeGeneratorService::generateFarmerCode();
            }
        });
    }

    /**
     * Get all transactions for this farmer
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active farmers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total weight from all transactions
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->transactions()->sum('weight_kg');
    }

    /**
     * Get total amount paid to this farmer
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->transactions()->sum('total_amount');
    }

    /**
     * Get transaction count
     */
    public function getTransactionCountAttribute(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Get average weight per transaction
     */
    public function getAverageWeightAttribute(): float
    {
        $count = $this->transaction_count;
        return $count > 0 ? $this->total_weight / $count : 0;
    }
}
