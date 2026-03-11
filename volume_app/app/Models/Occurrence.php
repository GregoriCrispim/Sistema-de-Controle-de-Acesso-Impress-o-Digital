<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occurrence extends Model
{
    protected $fillable = ['student_id', 'operator_id', 'type', 'description'];

    public function student() { return $this->belongsTo(Student::class); }
    public function operator() { return $this->belongsTo(User::class, 'operator_id'); }
}
