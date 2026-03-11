<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalValidation extends Model
{
    protected $fillable = [
        'fiscal_id', 'period_start', 'period_end', 'total_meals',
        'meal_value', 'total_value', 'biometric_count', 'manual_count',
        'protocol_number', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date',
            'meal_value' => 'decimal:2', 'total_value' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function fiscal() { return $this->belongsTo(User::class, 'fiscal_id'); }
}
