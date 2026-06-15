<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Modules\Core\Http\Requests\StoreUsuarioRequest;
use Modules\Core\Http\Requests\UpdateUsuarioRequest;
use Modules\Core\Repositories\PerfilRepository;
use Modules\Core\Repositories\UsuarioRepository;
use Modules\Core\Services\UsuarioService;
use Modules\Core\Support\PermissaoHelper;
use Spatie\Permission\Models\Permission;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioRepository $repository,
        private UsuarioService $service,
        private PerfilRepository $perfilRepository,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $filtros = request()->only(['busca', 'perfil', 'ativo']);
        $usuarios = $this->repository->listar($filtros);
        $perfis   = $this->perfilRepository->listar();

        return Inertia::render('Core/Usuarios/Index', compact('usuarios', 'perfis', 'filtros'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $perfis = $this->perfilRepository->listar();

        return Inertia::render('Core/Usuarios/Form', [
            'usuario' => null,
            'perfis'  => $perfis,
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $usuario = $this->service->criar($request->validated());

        return redirect()->route('core.usuarios.index')
            ->with('success', "Usuário {$usuario->name} criado com sucesso.");
    }

    public function edit(User $usuario)
    {
        $this->authorize('update', $usuario);

        $perfis = $this->perfilRepository->listar();
        $usuario->load('roles');

        return Inertia::render('Core/Usuarios/Form', [
            'usuario' => $usuario,
            'perfis'  => $perfis,
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        $this->service->atualizar($usuario, $request->validated());

        return redirect()->route('core.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $usuario)
    {
        $this->authorize('delete', $usuario);

        $this->service->desativar($usuario);

        return redirect()->route('core.usuarios.index')
            ->with('success', 'Usuário desativado.');
    }

    public function reativar(User $usuario)
    {
        $this->authorize('update', $usuario);

        $this->service->reativar($usuario);

        return redirect()->route('core.usuarios.index')
            ->with('success', 'Usuário reativado.');
    }

    public function editarPermissoes(User $usuario)
    {
        $this->authorize('editarPermissoes', $usuario);

        $usuario->load('roles', 'permissions');

        $todasPermissoes  = Permission::orderBy('name')->get();
        $agrupadas        = PermissaoHelper::agruparParaMatriz($todasPermissoes);
        $acoes            = PermissaoHelper::acoesUnicas($agrupadas);
        $permissoesPerfil = $usuario->getPermissionsViaRoles()->pluck('name')->toArray();
        $permissoesExtra  = $usuario->permissions->pluck('name')->toArray();
        $bloqueadas       = $usuario->permissions_bloqueadas ?? [];

        return Inertia::render('Core/Usuarios/Permissoes', compact(
            'usuario',
            'agrupadas',
            'acoes',
            'permissoesPerfil',
            'permissoesExtra',
            'bloqueadas',
        ));
    }

    public function salvarPermissoes(User $usuario)
    {
        $this->authorize('editarPermissoes', $usuario);

        $dados = request()->validate([
            'extras'    => ['array'],
            'extras.*'  => ['string', 'exists:permissions,name'],
            'bloqueadas'   => ['array'],
            'bloqueadas.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->service->salvarPermissoesCustomizadas(
            $usuario,
            $dados['extras'] ?? [],
            $dados['bloqueadas'] ?? [],
        );

        return redirect()->route('core.usuarios.permissoes', $usuario)
            ->with('success', 'Permissões salvas com sucesso.');
    }
}
