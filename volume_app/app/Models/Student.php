<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'enrollment_number', 'birth_date', 'course', 'class_name', 'photo_path', 'active'];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'active' => 'boolean'];
    }

    public function fingerprints() { return $this->hasMany(Fingerprint::class); }
    public function meals() { return $this->hasMany(Meal::class); }
    public function occurrences() { return $this->hasMany(Occurrence::class); }

    public function hasEatenToday(): bool
    {
        return $this->meals()
            ->whereDate('served_at', today())
            ->exists();
    }

    public function todayMeal()
    {
        return $this->meals()
            ->whereDate('served_at', today())
            ->first();
    }
}
