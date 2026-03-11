<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'operator_id', 'method', 'manual_reason', 'served_at', 'synced'];

    protected function casts(): array
    {
        return ['served_at' => 'datetime', 'synced' => 'boolean'];
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }

    public function scopeToday($query) { return $query->whereDate('served_at', today()); }
    public function scopeBiometric($query) { return $query->where('method', 'biometric'); }
    public function scopeManual($query) { return $query->where('method', 'manual'); }
    public function scopePeriod($query, $start, $end) { return $query->whereBetween('served_at', [$start, $end]); }
}
