<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'google_id', 'avatar', 'active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isOperator(): bool { return $this->role === 'operator'; }
    public function isCompany(): bool { return $this->role === 'company'; }
    public function isFiscal(): bool { return $this->role === 'fiscal'; }
    public function isManagement(): bool { return $this->role === 'management'; }

    public function meals() { return $this->hasMany(Meal::class, 'operator_id'); }
    public function occurrences() { return $this->hasMany(Occurrence::class, 'operator_id'); }
    public function fiscalValidations() { return $this->hasMany(FiscalValidation::class, 'fiscal_id'); }
    public function auditLogs() { return $this->hasMany(AuditLog::class); }
    public function loginLogs() { return $this->hasMany(LoginLog::class); }
}
