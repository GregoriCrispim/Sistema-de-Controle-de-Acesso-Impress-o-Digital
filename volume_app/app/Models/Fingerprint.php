<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fingerprint extends Model
{
    protected $fillable = ['student_id', 'template_code', 'finger_index'];

    public function student() { return $this->belongsTo(Student::class); }

    public static function getAllTemplatesForMatching(): array
    {
        return static::select('id', 'student_id', 'template_code')
            ->whereHas('student', fn ($q) => $q->where('active', true))
            ->get()
            ->toArray();
    }
}
