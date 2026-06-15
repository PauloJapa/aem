<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Http\Requests\StoreMenuRequest;
use Modules\Core\Http\Requests\UpdateMenuRequest;
use Modules\Core\Models\Menu;
use Modules\Core\Repositories\MenuRepository;
use Modules\Core\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(private MenuService $service) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Menu::class);

        $arvore = app(MenuRepository::class)->arvoreCompleta();

        return Inertia::render('Core/Menu/Index', [
            'arvore' => $arvore,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Menu::class);

        $pais = Menu::raiz()->ativo()->get(['id', 'label']);

        return Inertia::render('Core/Menu/Form', [
            'pais' => $pais,
            'menu' => null,
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $this->authorize('create', Menu::class);

        $this->service->criar($request->validated());

        return redirect()->route('core.menus.index')
                         ->with('success', 'Item de menu criado.');
    }

    public function edit(Menu $menu): Response
    {
        $this->authorize('update', $menu);

        $pais = Menu::raiz()->ativo()->where('id', '!=', $menu->id)->get(['id', 'label']);

        return Inertia::render('Core/Menu/Form', [
            'pais' => $pais,
            'menu' => $menu,
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->authorize('update', $menu);

        $this->service->atualizar($menu, $request->validated());

        return redirect()->route('core.menus.index')
                         ->with('success', 'Item de menu atualizado.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);

        try {
            $this->service->deletar($menu);
        } catch (\DomainException $e) {
            return redirect()->route('core.menus.index')
                             ->with('error', $e->getMessage());
        }

        return redirect()->route('core.menus.index')
                         ->with('success', 'Item de menu removido.');
    }

    public function reordenar(Request $request): JsonResponse
    {
        $this->authorize('reorder', Menu::class);

        $request->validate([
            'itens'             => ['required', 'array', 'min:1', 'max:100'],
            'itens.*.id'        => ['required', 'integer', 'exists:menus,id'],
            'itens.*.ordem'     => ['required', 'integer', 'min:0'],
            'itens.*.parent_id' => ['nullable', 'integer', 'exists:menus,id'],
        ]);

        try {
            $this->service->reordenar($request->input('itens'));
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Menu reordenado.']);
    }
}
