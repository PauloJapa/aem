# Tarefa: CRUD de Usuários + Perfis + Matriz de Permissões

## Contexto
Stack: Laravel 12 + Inertia.js + Vue 3 + PrimeVue 4 + Spatie Permission  
Módulo Core já criado. Spatie Permission já instalado e migrations rodadas.  
Padrão de permissions: `modulo.recurso.acao`

---

## Visão Geral da Arquitetura

```
Perfil (Role)
 └── permissions base (matriz do perfil)

Usuário
 ├── assignRole('gerente')              → herda permissions do perfil
 ├── givePermissionTo('dre.ver')        → libera extra além do perfil
 └── revokePermissionTo('vendas.excluir') → bloqueia mesmo tendo no perfil

$user->can('permissao')
 → Spatie resolve automaticamente as 3 fontes
 → Não precisa lógica customizada
```

---

## Segurança — regras obrigatórias

1. Middleware `auth` + `permission:core.usuarios.gerenciar` em todas as rotas
2. Policy `UsuarioPolicy` e `PerfilPolicy` por ação
3. `FormRequest` com validação em todo POST/PUT/PATCH
4. Admin não pode remover a própria role `admin` nem desativar a própria conta
5. Admin não pode editar outro usuário com role `admin` (proteção entre admins)
6. Permissions válidas são as que existem na tabela `permissions` — nunca aceitar string livre
7. Senhas: mínimo 8 chars, bcrypt — nunca logar nem expor
8. Soft delete em usuários — nunca deletar fisicamente
9. Toda alteração de permission/role registrada no Activitylog

---

## Arquivos a criar

```
Modules/Core/
├── database/
│   └── migrations/
│       └── xxxx_add_softdeletes_to_users_table.php   ← criar
├── Models/
│   └── Perfil.php                                    ← criar (wrapper do Role)
├── Policies/
│   ├── UsuarioPolicy.php                             ← criar
│   └── PerfilPolicy.php                              ← criar
├── Repositories/
│   ├── UsuarioRepository.php                         ← criar
│   └── PerfilRepository.php                          ← criar
├── Services/
│   ├── UsuarioService.php                            ← criar
│   └── PerfilService.php                             ← criar
├── Http/
│   ├── Controllers/
│   │   ├── UsuarioController.php                     ← criar
│   │   └── PerfilController.php                      ← criar
│   └── Requests/
│       ├── StoreUsuarioRequest.php                   ← criar
│       ├── UpdateUsuarioRequest.php                  ← criar
│       ├── StorePerfilRequest.php                    ← criar
│       └── UpdatePerfilRequest.php                   ← criar
└── routes/web.php                                    ← modificar

app/Models/
└── User.php                                          ← modificar (soft delete + traits)

resources/js/Pages/Core/
├── Usuarios/
│   ├── Index.vue                                     ← criar
│   ├── Form.vue                                      ← criar
│   └── Permissoes.vue                                ← criar (ajuste fino)
└── Perfis/
    ├── Index.vue                                     ← criar
    ├── Form.vue                                      ← criar
    └── Permissoes.vue                                ← criar (matriz do perfil)

resources/js/Components/Core/
└── MatrizPermissoes.vue                              ← criar (componente reutilizado)
```

---

## 1. Migration soft delete em users

```php
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes();
    $table->boolean('ativo')->default(true)->after('email');
    $table->string('telefone', 20)->nullable()->after('name');
    $table->string('avatar_url')->nullable()->after('telefone');
    $table->foreignId('role_id')->nullable()->after('avatar_url')
          ->comment('Role principal do usuário');
});
```

---

## 2. `User.php` — modificar model existente

Adicionar:
```php
use SoftDeletes;
use HasRoles;          // Spatie
use LogsActivity;      // Spatie Activitylog

protected $fillable = [
    'name', 'email', 'password', 'telefone', 'avatar_url', 'ativo',
];

protected $hidden = ['password', 'remember_token'];

protected $casts = [
    'ativo'             => 'boolean',
    'email_verified_at' => 'datetime',
    'password'          => 'hashed',
];

// Activitylog — registrar campos relevantes
protected static $recordEvents = ['created', 'updated', 'deleted'];
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['name', 'email', 'ativo'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

// Accessor: permissions efetivas (role + extras - bloqueadas)
public function getPermissoesEfetivasAttribute(): Collection
{
    return $this->getAllPermissions();
}
```

---

## 3. `Perfil.php` — wrapper semântico do Role

```php
namespace Modules\Core\Models;

// Não é uma tabela nova — é um wrapper do Role do Spatie
// para dar semântica de negócio ao conceito de "Perfil"

use Spatie\Permission\Models\Role;

class Perfil extends Role
{
    // Herda tudo do Role
    // Adiciona métodos de negócio aqui conforme necessário

    // Scope: perfis que podem ser atribuídos (não o super-admin)
    public function scopeAtribuivel($query)
    {
        return $query->where('name', '!=', 'super-admin');
    }
}
```

---

## 4. Policies

### `UsuarioPolicy.php`
```php
public function before(User $user): ?bool
{
    return $user->can('core.usuarios.gerenciar') ? true : null;
}

public function viewAny(User $user): bool  { return false; }
public function create(User $user): bool   { return false; }

public function update(User $user, User $alvo): bool
{
    // Admin não edita outro admin
    return ! $alvo->hasRole('admin') || $user->id === $alvo->id;
}

public function delete(User $user, User $alvo): bool
{
    // Não pode desativar a si mesmo
    return $user->id !== $alvo->id && ! $alvo->hasRole('admin');
}

public function editarPermissoes(User $user, User $alvo): bool
{
    return ! $alvo->hasRole('admin');
}
```

### `PerfilPolicy.php`
```php
public function before(User $user): ?bool
{
    return $user->can('core.usuarios.gerenciar') ? true : null;
}

public function viewAny(User $user): bool  { return false; }
public function create(User $user): bool   { return false; }

public function update(User $user, Role $perfil): bool
{
    // Perfil admin não pode ser editado
    return $perfil->name !== 'admin';
}

public function delete(User $user, Role $perfil): bool
{
    return $perfil->name !== 'admin' && $perfil->users()->count() === 0;
}
```

---

## 5. `UsuarioService.php`

```php
class UsuarioService
{
    public function __construct(private UsuarioRepository $usuarios) {}

    public function criar(array $dados): User
    {
        $perfil = Role::findByName($dados['perfil']);
        $usuario = $this->usuarios->criar(
            Arr::except($dados, ['perfil'])
        );
        $usuario->assignRole($perfil);

        activity()
            ->performedOn($usuario)
            ->withProperties(['perfil' => $perfil->name])
            ->log('usuario_criado');

        return $usuario;
    }

    public function atualizar(User $usuario, array $dados): User
    {
        if (isset($dados['perfil'])) {
            $perfil = Role::findByName($dados['perfil']);
            $usuario->syncRoles([$perfil]);
        }
        return $this->usuarios->atualizar(
            $usuario,
            Arr::except($dados, ['perfil'])
        );
    }

    public function desativar(User $usuario): void
    {
        if (! $usuario->ativo) return;
        $this->usuarios->atualizar($usuario, ['ativo' => false]);
        activity()->performedOn($usuario)->log('usuario_desativado');
    }

    public function reativar(User $usuario): void
    {
        $this->usuarios->atualizar($usuario, ['ativo' => true]);
        activity()->performedOn($usuario)->log('usuario_reativado');
    }

    // Ajuste fino de permissions do usuário
    // $extras: array de permission names a liberar além do perfil
    // $bloqueadas: array de permission names a revogar mesmo tendo no perfil
    public function salvarPermissoesCustomizadas(
        User $usuario,
        array $extras,
        array $bloqueadas
    ): void {
        // Validar que todas as permissions existem
        $todasPermissions = Permission::whereIn('name', array_merge($extras, $bloqueadas))
                                      ->pluck('name')
                                      ->toArray();

        $invalidas = array_diff(array_merge($extras, $bloqueadas), $todasPermissions);
        if (! empty($invalidas)) {
            throw new \DomainException('Permissions inválidas: ' . implode(', ', $invalidas));
        }

        // Não pode ter a mesma permission em extras e bloqueadas
        $conflito = array_intersect($extras, $bloqueadas);
        if (! empty($conflito)) {
            throw new \DomainException('Permission não pode ser extra e bloqueada ao mesmo tempo.');
        }

        // Sincronizar permissions diretas do usuário
        // (Spatie: permissions diretas sobrescrevem o role)
        $usuario->syncPermissions($extras);

        // Registrar bloqueadas em campo customizado ou tabela auxiliar
        // Spatie não tem conceito de "permission bloqueada" nativo
        // Solução: salvar em cache/coluna JSON no usuário
        $usuario->forceFill([
            'permissions_bloqueadas' => $bloqueadas
        ])->save();

        activity()
            ->performedOn($usuario)
            ->withProperties(compact('extras', 'bloqueadas'))
            ->log('permissoes_customizadas_salvas');
    }
}
```

> **Nota sobre permissions bloqueadas:** O Spatie não tem suporte nativo a "revogar permission herdada de role". A solução é salvar as bloqueadas em coluna `permissions_bloqueadas` (JSON) no usuário e sobrescrever o método `can()` no `User.php`:

```php
// Em User.php — sobrescrever can() para considerar bloqueadas
public function can($ability, $arguments = []): bool
{
    $bloqueadas = $this->permissions_bloqueadas ?? [];
    if (in_array($ability, $bloqueadas)) {
        return false;
    }
    return parent::can($ability, $arguments);
}
```

Adicionar à migration de users:
```php
$table->json('permissions_bloqueadas')->nullable()->default(null);
```

---

## 6. `PerfilService.php`

```php
class PerfilService
{
    public function criar(array $dados): Role
    {
        $perfil = Role::create(['name' => $dados['name'], 'guard_name' => 'web']);
        activity()->performedOn($perfil)->log('perfil_criado');
        return $perfil;
    }

    public function salvarPermissoes(Role $perfil, array $permissions): void
    {
        // Validar que todas existem
        $validas = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $invalidas = array_diff($permissions, $validas);
        if (! empty($invalidas)) {
            throw new \DomainException('Permissions inválidas: ' . implode(', ', $invalidas));
        }

        $perfil->syncPermissions($permissions);

        activity()
            ->performedOn($perfil)
            ->withProperties(['permissions' => $permissions])
            ->log('permissoes_perfil_atualizadas');
    }

    public function deletar(Role $perfil): void
    {
        if ($perfil->users()->count() > 0) {
            throw new \DomainException(
                "Perfil possui {$perfil->users()->count()} usuário(s) vinculado(s). Remova-os antes de excluir."
            );
        }
        activity()->performedOn($perfil)->log('perfil_deletado');
        $perfil->delete();
    }
}
```

---

## 7. FormRequests

### `StoreUsuarioRequest.php`
```php
public function rules(): array
{
    return [
        'name'     => ['required', 'string', 'max:100'],
        'email'    => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'telefone' => ['nullable', 'string', 'max:20'],
        'perfil'   => ['required', 'string', 'exists:roles,name'],
        'ativo'    => ['boolean'],
    ];
}
```

### `UpdateUsuarioRequest.php`
```php
public function rules(): array
{
    return [
        'name'     => ['required', 'string', 'max:100'],
        'email'    => ['required', 'email', Rule::unique('users')->ignore($this->route('usuario'))],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        'telefone' => ['nullable', 'string', 'max:20'],
        'perfil'   => ['required', 'string', 'exists:roles,name'],
        'ativo'    => ['boolean'],
    ];
}
```

### `StorePerfilRequest.php`
```php
public function rules(): array
{
    return [
        'name'  => ['required', 'string', 'max:60', 'unique:roles,name',
                    'regex:/^[a-z0-9\-\_]+$/'],  // só lowercase, hifens e underscores
        'label' => ['required', 'string', 'max:60'],  // nome amigável para exibição
    ];
}
```

---

## 8. Controllers

### `UsuarioController.php`
```php
// index()   → lista paginada (20/pág) com filtro por nome/email/perfil/ativo
//             Inertia: 'Core/Usuarios/Index'
//             props: { usuarios: paginate, perfis: Role::all() }

// create()  → Inertia: 'Core/Usuarios/Form'
//             props: { usuario: null, perfis: Role::atribuivel()->get() }

// store()   → StoreUsuarioRequest → service->criar() → redirect index

// edit()    → Inertia: 'Core/Usuarios/Form'
//             props: { usuario: $usuario->load('roles'), perfis }

// update()  → UpdateUsuarioRequest → service->atualizar() → redirect index

// destroy() → soft delete via service->desativar() → redirect index

// permissoes() → GET /core/usuarios/{usuario}/permissoes
//   Inertia: 'Core/Usuarios/Permissoes'
//   props: {
//     usuario: com roles e permissions,
//     todasPermissions: agrupadas por modulo,
//     permissoesHerdadas: do perfil,
//     permissoesExtras: diretas do usuário,
//     permissoesBloqueadas: do campo JSON,
//   }

// salvarPermissoes() → PATCH /core/usuarios/{usuario}/permissoes
//   Valida arrays extras[] e bloqueadas[]
//   → service->salvarPermissoesCustomizadas()
```

### `PerfilController.php`
```php
// index()   → lista todos os perfis com count de usuários
//             Inertia: 'Core/Perfis/Index'

// create()  → Inertia: 'Core/Perfis/Form'
//             props: { perfil: null }

// store()   → StorePerfilRequest → service->criar() → redirect permissoes do perfil

// edit()    → Inertia: 'Core/Perfis/Form'
//             props: { perfil }

// update()  → UpdatePerfilRequest → service->atualizar() → redirect index

// destroy() → service->deletar() (valida sem usuários) → redirect index

// permissoes() → GET /core/perfis/{perfil}/permissoes
//   Inertia: 'Core/Perfis/Permissoes'
//   props: {
//     perfil: com permissions,
//     todasPermissions: agrupadas por modulo (para montar a matriz)
//   }

// salvarPermissoes() → PUT /core/perfis/{perfil}/permissoes
//   Valida permissions[] → service->salvarPermissoes()
```

---

## 9. Rotas

```php
Route::middleware(['auth', 'permission:core.usuarios.gerenciar'])
     ->prefix('core')
     ->name('core.')
     ->group(function () {

         // Usuários
         Route::resource('usuarios', UsuarioController::class)->except(['show']);
         Route::get('usuarios/{usuario}/permissoes',
                    [UsuarioController::class, 'permissoes'])->name('usuarios.permissoes');
         Route::patch('usuarios/{usuario}/permissoes',
                      [UsuarioController::class, 'salvarPermissoes'])->name('usuarios.permissoes.salvar');

         // Perfis
         Route::resource('perfis', PerfilController::class)->except(['show']);
         Route::get('perfis/{perfil}/permissoes',
                    [PerfilController::class, 'permissoes'])->name('perfis.permissoes');
         Route::put('perfis/{perfil}/permissoes',
                    [PerfilController::class, 'salvarPermissoes'])->name('perfis.permissoes.salvar');
     });
```

---

## 10. `MatrizPermissoes.vue` — componente reutilizável

Usado tanto em `Perfis/Permissoes.vue` quanto em `Usuarios/Permissoes.vue`.

### Props
```js
defineProps({
  // Todas as permissions agrupadas por módulo
  // Estrutura:
  // {
  //   Financeiro: {
  //     'Contas a Pagar': ['visualizar','criar','editar','excluir'],
  //     'Contas a Receber': ['visualizar','criar','editar','excluir'],
  //   },
  //   Vendas: { ... }
  // }
  permissionsAgrupadas: Object,

  // Modo 'perfil': checkboxes simples (marcado = tem / desmarcado = não tem)
  // Modo 'usuario': 3 estados por célula (herdado/extra/bloqueado)
  modo: { type: String, default: 'perfil' },

  // Permissions que o perfil do usuário concede (só no modo 'usuario')
  herdadas: { type: Array, default: () => [] },

  // Permissions extras diretas do usuário (só no modo 'usuario')
  extras: { type: Array, default: () => [] },

  // Permissions bloqueadas do usuário (só no modo 'usuario')
  bloqueadas: { type: Array, default: () => [] },

  // Permissions do perfil selecionado (modo 'perfil')
  selecionadas: { type: Array, default: () => [] },

  // Readonly — apenas visualização
  readonly: { type: Boolean, default: false },
})

const emit = defineEmits(['update:selecionadas', 'update:extras', 'update:bloqueadas'])
```

### Visual — Modo Perfil (checkboxes simples)

```
┌──────────────────────────────────────────────────────────┐
│ FINANCEIRO                                               │
├────────────────────┬───────────┬───────┬────────┬────────┤
│ Recurso            │ Visualizar│ Criar │ Editar │Excluir │
├────────────────────┼───────────┼───────┼────────┼────────┤
│ Contas a Pagar     │    ☑      │  ☑    │   ☐    │   ☐    │
│ Contas a Receber   │    ☑      │  ☐    │   ☐    │   ☐    │
│ Fluxo de Caixa     │    ☑      │  ☐    │   ☐    │   ☐    │
├────────────────────┴───────────┴───────┴────────┴────────┤
│ VENDAS                                                   │
├────────────────────┬───────────┬───────┬────────┬────────┤
│ Pedidos            │    ☑      │  ☑    │   ☑    │   ☐    │
└────────────────────┴───────────┴───────┴────────┴────────┘
```

- Checkbox no cabeçalho do módulo seleciona/deseleciona tudo do módulo
- Checkbox no cabeçalho da coluna seleciona/deseleciona a ação em todos os recursos

### Visual — Modo Usuário (3 estados)

Cada célula é um botão cíclico que alterna entre 3 estados ao clicar:

```
┌──────────────────────────────────────────────────────────┐
│ Legenda: ⬜ herdado do perfil  🟢 liberado extra  🔴 bloqueado  │
├────────────────────┬───────────┬───────┬────────┬────────┤
│ Recurso            │ Visualizar│ Criar │ Editar │Excluir │
├────────────────────┼───────────┼───────┼────────┼────────┤
│ Contas a Pagar     │    ⬜      │  ⬜    │  🟢    │  🔴    │
│ Contas a Receber   │    ⬜      │  🔴   │  🔴    │  🔴    │
└────────────────────┴───────────┴───────┴────────┴────────┘
```

Estados e significado:
- `⬜` cinza — herdado do perfil, sem modificação (clique → vira 🟢)
- `🟢` verde — liberado extra além do perfil (clique → vira 🔴)
- `🔴` vermelho — bloqueado, mesmo que o perfil conceda (clique → volta ⬜)

Se a permission não existe no perfil:
- `⬜` cinza claro — não tem e não é extra (clique → vira 🟢)
- `🟢` verde — liberado diretamente (clique → volta ⬜)
- Nunca mostra 🔴 para permission que não está no perfil (não faz sentido bloquear o que não tem)

---

## 11. Pages Vue

### `Perfis/Index.vue`
```
┌─────────────────────────────────────────────────────┐
│ Perfis de Acesso                    [+ Novo Perfil] │
├──────────┬──────────────────┬────────────┬──────────┤
│ Nome     │ Label            │ Usuários   │ Ações    │
├──────────┼──────────────────┼────────────┼──────────┤
│ admin    │ Administrador    │ 1          │ [🔑][✏️] │
│ gerente  │ Gerente          │ 3          │ [🔑][✏️][🗑️]│
│ operador │ Operador         │ 12         │ [🔑][✏️][🗑️]│
└──────────┴──────────────────┴────────────┴──────────┘
```
- `[🔑]` → vai para `core.perfis.permissoes` (tela da matriz)
- `[🗑️]` só aparece se `usuarios_count === 0`
- Perfil `admin` não tem botão editar nem excluir

### `Perfis/Permissoes.vue`
```
┌─────────────────────────────────────────────────────┐
│ ← Perfis   Permissions — Gerente                   │
├─────────────────────────────────────────────────────┤
│  <MatrizPermissoes modo="perfil" ... />             │
├─────────────────────────────────────────────────────┤
│                              [Cancelar]  [Salvar]   │
└─────────────────────────────────────────────────────┘
```

### `Usuarios/Index.vue`
```
┌──────────────────────────────────────────────────────────┐
│ Usuários                              [+ Novo Usuário]   │
├──────────────┬──────────────────┬────────────────────────┤
│ Filtros:     │ [Nome/Email    ] │ [Perfil ▾] [Status ▾] │
├──────┬───────┴──────────────────┴────────────────────────┤
│Avatar│ Nome          │ Email           │ Perfil  │ Ativo │ Ações
├──────┼───────────────┼─────────────────┼─────────┼───────┼──────┤
│  JS  │ João Silva    │ joao@erp.dev    │ gerente │  ✅   │[🔑][✏️][⊘]│
│  MT  │ Maria Teste   │ maria@erp.dev   │ operador│  ✅   │[🔑][✏️][⊘]│
└──────┴───────────────┴─────────────────┴─────────┴───────┴──────┘
```
- Avatar com iniciais (componente `AppAvatar`)
- `[🔑]` → `core.usuarios.permissoes` (ajuste fino)
- `[⊘]` → desativa/reativa o usuário (toggle, com confirmação)
- Filtros reativos — usam `router.get` com `preserveState: true`

### `Usuarios/Form.vue`
Campos: Nome, Email, Senha (+ confirmar, só obrigatório no create), Telefone, Perfil (Select), Ativo (Toggle)

### `Usuarios/Permissoes.vue`
```
┌──────────────────────────────────────────────────────────┐
│ ← Usuários   Permissões — João Silva                    │
├──────────────────────────────────────────────────────────┤
│ Perfil atual: Gerente   [trocar perfil aqui se quiser]  │
├──────────────────────────────────────────────────────────┤
│ ⬜ herdado do perfil  🟢 liberado extra  🔴 bloqueado   │
│                                                          │
│  <MatrizPermissoes modo="usuario" ... />                 │
├──────────────────────────────────────────────────────────┤
│                              [Cancelar]  [Salvar]        │
└──────────────────────────────────────────────────────────┘
```

---

## 12. Agrupar permissions para a matriz

Helper no backend — criar método estático em `Permission` ou em helper de suporte:

```php
// Agrupa as permissions pelo padrão modulo.recurso.acao
// Entrada:  ['financeiro.contas-pagar.visualizar', 'financeiro.contas-pagar.criar', ...]
// Saída:
// [
//   'Financeiro' => [
//     'Contas a Pagar' => ['visualizar', 'criar', 'editar', 'excluir'],
//   ]
// ]

public static function agruparParaMatriz(Collection $permissions): array
{
    $resultado = [];

    foreach ($permissions as $perm) {
        $partes = explode('.', $perm->name);
        if (count($partes) < 3) continue;

        [$modulo, $recurso, $acao] = $partes;

        $moduloLabel  = Str::title(str_replace('-', ' ', $modulo));
        $recursoLabel = Str::title(str_replace('-', ' ', $recurso));

        $resultado[$moduloLabel][$recursoLabel][] = $acao;
    }

    ksort($resultado);
    return $resultado;
}
```

---

## 13. Seeder — adicionar permissions de gestão

No `CoreSeeder.php` existente, adicionar:

```php
// Permissions de gestão do sistema (só admin)
$permissoesCore = [
    'core.usuarios.gerenciar',
    'core.perfis.gerenciar',
    'core.menu.gerenciar',
];

foreach ($permissoesCore as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$admin->givePermissionTo($permissoesCore);

// Adicionar label amigável ao Role (campo extra)
// Requer coluna 'label' na tabela roles:
Role::where('name', 'admin')->update(['label' => 'Administrador']);
Role::where('name', 'gerente')->update(['label' => 'Gerente']);
Role::where('name', 'operador')->update(['label' => 'Operador']);
```

Migration para adicionar `label` na tabela `roles`:
```php
Schema::table('roles', function (Blueprint $table) {
    $table->string('label', 60)->nullable()->after('name');
});
```

---

## Ordem de execução

1. Criar migrations (soft delete users + label roles) e rodar
2. Atualizar `User.php` (SoftDeletes, can() override, permissions_bloqueadas)
3. Criar `Perfil.php`
4. Criar `UsuarioPolicy.php` e `PerfilPolicy.php` — registrar no `CoreServiceProvider`
5. Criar `UsuarioRepository.php` e `PerfilRepository.php`
6. Criar `UsuarioService.php` e `PerfilService.php`
7. Criar os 4 FormRequests
8. Criar `UsuarioController.php` e `PerfilController.php`
9. Registrar rotas
10. Criar `MatrizPermissoes.vue`
11. Criar `Perfis/Index.vue`, `Perfis/Form.vue`, `Perfis/Permissoes.vue`
12. Criar `Usuarios/Index.vue`, `Usuarios/Form.vue`, `Usuarios/Permissoes.vue`
13. Atualizar `CoreSeeder` e rodar

---

## Verificação final

- [ ] Usuário sem `core.usuarios.gerenciar` não acessa nenhuma rota (retorna 403)
- [ ] Admin não consegue desativar a própria conta
- [ ] Admin não consegue editar outro admin
- [ ] Perfil `admin` não aparece nos botões de editar/excluir
- [ ] Criar perfil com nome maiúsculo ou espaço retorna erro de validação
- [ ] Deletar perfil com usuários vinculados retorna erro amigável
- [ ] Matriz do perfil: selecionar módulo inteiro funciona
- [ ] Matriz do perfil: selecionar coluna inteira funciona
- [ ] Salvar permissions do perfil reflete imediatamente no menu dos usuários desse perfil
- [ ] Modo usuário: 3 estados funcionam corretamente (herdado → extra → bloqueado → herdado)
- [ ] Permission bloqueada no usuário retorna false em `$user->can()` mesmo com role ativa
- [ ] Permission extra no usuário retorna true em `$user->can()` mesmo sem role
- [ ] Todas as alterações de permission aparecem no log de auditoria (Activitylog)
- [ ] Filtros na listagem de usuários funcionam com `preserveState`
- [ ] Soft delete: usuário desativado não consegue logar
- [ ] Senhas nunca expostas em nenhuma prop do Inertia

## O que foi criado/alterado:

- Backend (PHP)
- 
- StoreUsuarioRequest / UpdateUsuarioRequest / StorePerfilRequest / UpdatePerfilRequest — 4 FormRequests com - regras de validação
- UsuarioController — CRUD + reativar + editarPermissoes + salvarPermissoes
- PerfilController — CRUD + editarPermissoes + salvarPermissoes
- routes/web.php — rotas para /core/usuarios e /core/perfis (com parâmetro {perfil} correto via .parameters- (['perfis' => 'perfil']))
- RouteServiceProvider — binding Route::model('perfil', Role::class)
- CoreSeeder — labels das roles (Administrador, Gerente, Operador), permissão core.usuarios.gerenciar, menu - "Administração" com filhos (Usuários, Perfis, Menus)
- Frontend (Vue)
- 
- Components/Core/MatrizPermissoes.vue — tabela genérica de permissões com slot #cell para customização
- Pages/Core/Perfis/Index.vue — listagem com contagem de usuários, botões de editar/permissões/excluir
- Pages/Core/Perfis/Form.vue — criar/editar perfil
- Pages/Core/Perfis/Permissoes.vue — matriz de permissões do perfil
- Pages/Core/Usuarios/Index.vue — listagem com filtros (busca, perfil, status), paginação
- Pages/Core/Usuarios/Form.vue — criar/editar usuário (com campo password + ToggleSwitch ativo)
- Pages/Core/Usuarios/Permissoes.vue — matriz com 3 estados por permissão: herdada do perfil (azul), extra - (checkbox verde) e bloqueada (checkbox vermelho)