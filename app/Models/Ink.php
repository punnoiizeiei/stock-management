<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ink extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand',
        'model',
        'color',
        'color_code',
        'type',
        'unit',
        'compatible_printers',
        'location',
        'current_stock',
        'min_stock',
        'price_per_unit',
        'notes',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'min_stock' => 'integer',
        'price_per_unit' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InkTransaction::class)->orderBy('action_date', 'desc')->orderBy('id', 'desc');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock > 0 && $this->current_stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out';
        }
        if ($this->current_stock <= $this->min_stock) {
            return 'low';
        }

        return 'normal';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out' => 'หมดสต็อก',
            'low' => 'ใกล้หมด',
            default => 'ปกติ',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'out' => 'bg-danger text-white',
            'low' => 'bg-warning text-dark',
            default => 'bg-success text-white',
        };
    }
}
