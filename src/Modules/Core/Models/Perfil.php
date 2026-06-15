<?php

namespace Modules\Core\Models;

use Spatie\Permission\Models\Role;

class Perfil extends Role
{
    protected $fillable = ['name', 'label', 'guard_name'];

    public function scopeAtribuivel($query)
    {
        return $query->where('name', '!=', 'super-admin');
    }
}
