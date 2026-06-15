<?php

namespace Modules\Core\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Core\Models\Menu;
use Modules\Core\Repositories\MenuRepository;

class MenuService
{
    public function __construct(private MenuRepository $menus) {}

    public function criar(array $dados): Menu
    {
        return $this->menus->criar($dados);
    }

    public function atualizar(Menu $menu, array $dados): Menu
    {
        return $this->menus->atualizar($menu, $dados);
    }

    public function deletar(Menu $menu): void
    {
        if ($menu->filhos()->ativo()->exists()) {
            throw new \DomainException('Não é possível excluir um item com subitens ativos.');
        }
        $this->menus->deletar($menu);
    }

    public function reordenar(array $itens): void
    {
        $ids = array_column($itens, 'id');
        $existentes = Menu::whereIn('id', $ids)->count();

        if ($existentes !== count($ids)) {
            throw new \DomainException('Itens de menu inválidos na reordenação.');
        }

        $parentIds = array_filter(array_column($itens, 'parent_id'));
        if (! empty($parentIds)) {
            $parentExistentes = Menu::whereIn('id', $parentIds)->count();
            if ($parentExistentes !== count(array_unique($parentIds))) {
                throw new \DomainException('Parent inválido na reordenação.');
            }
        }

        foreach ($itens as $item) {
            if (! empty($item['parent_id'])) {
                $pai = Menu::find($item['parent_id']);
                if ($pai && $pai->parent_id !== null) {
                    throw new \DomainException('Profundidade máxima de menu é 2 níveis.');
                }
            }
        }

        $this->menus->reordenar($itens);
    }

    public function arvoreParaUsuario(User $user): array
    {
        try {
            $arvore = $this->menus->arvoreAtiva();
            return $this->filtrar($arvore, $user);
        } catch (\Exception) {
            return [];
        }
    }

    private function filtrar(Collection $itens, User $user): array
    {
        $resultado = [];

        foreach ($itens as $item) {
            if ($item->filhos->isNotEmpty()) {
                $filhosFiltrados = $this->filtrar($item->filhos, $user);
                if (! empty($filhosFiltrados)) {
                    $resultado[] = [
                        'label'  => $item->label,
                        'icon'   => $item->icon,
                        'filhos' => $filhosFiltrados,
                    ];
                }
                continue;
            }

            if (! $item->permission || $user->can($item->permission)) {
                $resultado[] = [
                    'label' => $item->label,
                    'icon'  => $item->icon,
                    'rota'  => $item->rota,
                ];
            }
        }

        return $resultado;
    }
}
