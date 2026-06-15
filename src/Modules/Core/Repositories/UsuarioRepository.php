<?php

namespace Modules\Core\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UsuarioRepository
{
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $query = User::with('roles')->withTrashed(false);

        if (! empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $query->where(fn ($q) => $q->where('name', 'ilike', $busca)
                                       ->orWhere('email', 'ilike', $busca));
        }

        if (! empty($filtros['perfil'])) {
            $query->role($filtros['perfil']);
        }

        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $query->where('ativo', (bool) $filtros['ativo']);
        }

        return $query->orderBy('name')->paginate(20)->withQueryString();
    }

    public function criar(array $dados): User
    {
        return User::create($dados);
    }

    public function atualizar(User $usuario, array $dados): User
    {
        $usuario->update($dados);
        return $usuario->fresh();
    }

    public function desativar(User $usuario): void
    {
        $usuario->update(['ativo' => false]);
    }

    public function reativar(User $usuario): void
    {
        $usuario->update(['ativo' => true]);
    }
}
