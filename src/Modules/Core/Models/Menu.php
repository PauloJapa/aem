<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id', 'label', 'icon', 'rota', 'permission', 'ordem', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function pai(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function filhos(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('ordem');
    }

    public function scopeAtivo(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeRaiz(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('ordem');
    }
}
