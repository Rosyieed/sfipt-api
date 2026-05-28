<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'location',
        'type',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function incomingStockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class, 'to_warehouse_id');
    }

    public function outgoingStockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class, 'from_warehouse_id');
    }
}
