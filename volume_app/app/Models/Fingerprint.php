<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fingerprint extends Model
{
    protected $fillable = ['student_id', 'template_code', 'finger_index'];

    public function student() { return $this->belongsTo(Student::class); }

    public static function findByTemplate(string $templateCode): ?self
    {
        return static::where('template_code', $templateCode)->first();
    }
}
