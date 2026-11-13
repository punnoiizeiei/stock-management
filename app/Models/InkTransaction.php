<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InkTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'ink_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'person_name',
        'department_id',
        'department_name',
        'reference_no',
        'printer_target',
        'action_date',
        'notes',
    ];

    protected $casts = [
        'action_date' => 'date',
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function ink(): BelongsTo
    {
        return $this->belongsTo(Ink::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'รับเข้าสต็อก',
            'out' => 'เบิกใช้งาน',
            'adjust' => 'ปรับสต็อก',
            default => $this->type,
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'in' => 'bg-success text-white',
            'out' => 'bg-primary text-white',
            'adjust' => 'bg-secondary text-white',
            default => 'bg-dark text-white',
        };
    }
}
