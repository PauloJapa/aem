<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'avatar_url',
        'ativo',
        'permissions_bloqueadas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'ativo'                  => 'boolean',
            'permissions_bloqueadas' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'ativo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Respeita permissions bloqueadas individualmente
    public function can($ability, $arguments = []): bool
    {
        if (is_string($ability)) {
            $bloqueadas = $this->permissions_bloqueadas ?? [];
            if (in_array($ability, (array) $bloqueadas)) {
                return false;
            }
        }
        return parent::can($ability, $arguments);
    }
}
