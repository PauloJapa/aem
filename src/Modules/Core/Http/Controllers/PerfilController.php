<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Core\Http\Requests\StorePerfilRequest;
use Modules\Core\Http\Requests\UpdatePerfilRequest;
use Modules\Core\Repositories\PerfilRepository;
use Modules\Core\Services\PerfilService;
use Modules\Core\Support\PermissaoHelper;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerfilController extends Controller
{
    public function __construct(
        private PerfilRepository $repository,
        private PerfilService $service,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $perfis = $this->repository->listar();

        return Inertia::render('Core/Perfis/Index', compact('perfis'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Core/Perfis/Form', ['perfil' => null]);
    }

    public function store(StorePerfilRequest $request)
    {
        $perfil = $this->service->criar($request->validated());

        return redirect()->route('core.perfis.index')
            ->with('success', "Perfil {$perfil->label} criado com sucesso.");
    }

    public function edit(Role $perfil)
    {
        $this->authorize('update', $perfil);

        return Inertia::render('Core/Perfis/Form', compact('perfil'));
    }

    public function update(UpdatePerfilRequest $request, Role $perfil)
    {
        $this->service->atualizar($perfil, $request->validated());

        return redirect()->route('core.perfis.index')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function destroy(Role $perfil)
    {
        $this->authorize('delete', $perfil);

        try {
            $this->service->deletar($perfil);
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('core.perfis.index')
            ->with('success', 'Perfil excluído com sucesso.');
    }

    public function editarPermissoes(Role $perfil)
    {
        $this->authorize('update', $perfil);

        $perfil->load('permissions');

        $todasPermissoes     = Permission::orderBy('name')->get();
        $agrupadas           = PermissaoHelper::agruparParaMatriz($todasPermissoes);
        $acoes               = PermissaoHelper::acoesUnicas($agrupadas);
        $permissoesAtivas    = $perfil->permissions->pluck('name')->toArray();

        return Inertia::render('Core/Perfis/Permissoes', compact(
            'perfil',
            'agrupadas',
            'acoes',
            'permissoesAtivas',
        ));
    }

    public function salvarPermissoes(Role $perfil)
    {
        $this->authorize('update', $perfil);

        $dados = request()->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        try {
            $this->service->salvarPermissoes($perfil, $dados['permissions'] ?? []);
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('core.perfis.permissoes', $perfil)
            ->with('success', 'Permissões do perfil salvas com sucesso.');
    }
}
