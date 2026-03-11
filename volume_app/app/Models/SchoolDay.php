<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolDay extends Model
{
    protected $fillable = ['date', 'is_school_day'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_school_day' => 'boolean'];
    }
}
