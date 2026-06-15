<?php

namespace Modules\Core\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Menu;

class MenuRepository
{
    public function arvoreCompleta(): Collection
    {
        return Menu::with('filhos')->raiz()->get();
    }

    public function arvoreAtiva(): Collection
    {
        return Menu::with(['filhos' => fn ($q) => $q->ativo()->orderBy('ordem')])
                   ->raiz()
                   ->ativo()
                   ->get();
    }

    public function criar(array $dados): Menu
    {
        $dados['ordem'] = Menu::where('parent_id', $dados['parent_id'] ?? null)->max('ordem') + 1;
        return Menu::create($dados);
    }

    public function atualizar(Menu $menu, array $dados): Menu
    {
        $menu->update($dados);
        return $menu->fresh();
    }

    public function deletar(Menu $menu): void
    {
        $menu->delete();
    }

    public function reordenar(array $itens): void
    {
        DB::transaction(function () use ($itens) {
            foreach ($itens as $item) {
                Menu::where('id', $item['id'])->update([
                    'ordem'     => $item['ordem'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
            }
        });
    }
}
