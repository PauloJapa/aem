# Tarefa: Gerenciamento de Menu do Sistema

**Status: CONCLUÍDA**

## Contexto
Stack: Laravel 12 + Inertia.js + Vue 3 + PrimeVue 4 + Tailwind CSS + Spatie Permission  
Módulo Core em `Modules/Core/`  
`HandleInertiaRequests.php` atualizado para ler menu do banco via `MenuService`

---

## Dependência instalada

```bash
# rodado no container vite
npm install vue-draggable-plus
```

---

## Segurança — regras implementadas

1. Middleware `auth` + `permission:core.menu.gerenciar` em todas as rotas do grupo
2. `MenuPolicy` com hook `before()` — quem tem a permission passa em tudo
3. `FormRequest` em todo POST/PUT (`StoreMenuRequest`, `UpdateMenuRequest`)
4. Model binding nas rotas (`{menu}`) — nunca ID exposto sem binding
5. `ordem` e `parent_id` revalidados no `MenuService::reordenar()` antes de persistir
6. `rota` salva como nome de rota Laravel, nunca URL direta

---

## Arquivos criados / modificados

```
Modules/Core/
├── database/migrations/
│   └── 2026_06_14_000001_create_menus_table.php  ✓
├── Models/
│   └── Menu.php                                   ✓
├── Policies/
│   └── MenuPolicy.php                             ✓
├── Repositories/
│   └── MenuRepository.php                         ✓
├── Services/
│   └── MenuService.php                            ✓
├── Http/
│   ├── Controllers/
│   │   └── MenuController.php                     ✓
│   └── Requests/
│       ├── StoreMenuRequest.php                   ✓
│       └── UpdateMenuRequest.php                  ✓
├── Providers/
│   └── CoreServiceProvider.php                    ✓ (registra MenuPolicy)
└── routes/
    └── web.php                                    ✓ (rotas core.menus.*)

app/Http/Middleware/
└── HandleInertiaRequests.php                      ✓ (usa MenuService)

Modules/Core/Database/Seeders/
└── CoreSeeder.php                                 ✓ (permission + 20 itens de menu)

resources/js/
├── Components/Layout/
│   └── AppSidebar.vue                             ✓ (corrigido toggleSubmenu)
└── Pages/Core/Menu/
    ├── Index.vue                                  ✓
    ├── Form.vue                                   ✓
    └── Components/
        ├── MenuTree.vue                           ✓
        └── IconPicker.vue                         ✓
```

---

## 1. Migration `create_menus_table`

```php
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')
          ->nullable()
          ->constrained('menus')
          ->nullOnDelete();
    $table->string('label', 80);
    $table->string('icon', 60)->default('pi pi-circle');
    $table->string('rota', 120)->nullable();
    $table->string('permission', 120)->nullable();
    $table->unsignedSmallInteger('ordem')->default(0);
    $table->boolean('ativo')->default(true);
    $table->timestamps();

    $table->index(['parent_id', 'ordem', 'ativo']);
});
```

Rodar: `php artisan module:migrate Core`

---

## 2. Menu.php (Model)

- `fillable`: parent_id, label, icon, rota, permission, ordem, ativo
- `casts`: ativo → boolean, ordem → integer
- `pai()` → belongsTo Menu
- `filhos()` → hasMany Menu, ordenado por `ordem`
- `scopeAtivo()` — filtra `ativo = true`
- `scopeRaiz()` — filtra `parent_id IS NULL`, ordenado por `ordem`

---

## 3. MenuPolicy.php

Hook `before()`: se o usuário tem `core.menu.gerenciar` → retorna `true` (libera tudo).  
Todos os outros métodos retornam `false` por padrão (bloqueio total sem a permission).

Registrada no `CoreServiceProvider::boot()`:
```php
Gate::policy(Menu::class, MenuPolicy::class);
```

---

## 4. MenuRepository.php

| Método | Descrição |
|---|---|
| `arvoreCompleta()` | Raiz + filhos eager loaded — para tela de gerenciamento |
| `arvoreAtiva()` | Só itens ativos — para menu do usuário |
| `criar(array)` | Auto-incrementa `ordem` com `max() + 1` |
| `atualizar(Menu, array)` | Update + `fresh()` |
| `deletar(Menu)` | Delete simples (FK `nullOnDelete` cuida dos filhos) |
| `reordenar(array)` | Transação com update de `ordem` e `parent_id` |

---

## 5. MenuService.php

| Método | Regra de negócio |
|---|---|
| `criar()` | Delega ao repository |
| `atualizar()` | Delega ao repository |
| `deletar()` | Bloqueia se tiver filhos ativos (`DomainException`) |
| `reordenar()` | Valida IDs, parent_ids e profundidade máx 2 níveis antes de persistir |
| `arvoreParaUsuario(User)` | Carrega árvore ativa e filtra por permission recursivamente. Try-catch para quando tabela ainda não existe |

---

## 6. StoreMenuRequest / UpdateMenuRequest

```php
'label'      => ['required', 'string', 'max:80'],
'icon'       => ['required', 'string', 'max:60', 'regex:/^pi pi-[a-z0-9-]+$/'],
'parent_id'  => ['nullable', 'exists:menus,id'],
'rota'       => ['nullable', 'string', 'max:120'],
'permission' => ['nullable', 'string', 'max:120'],
'ativo'      => ['boolean'],
```

`withValidator()` em ambos:
- `StoreMenuRequest`: bloqueia `parent_id` apontando para item que já tem pai (evita 3+ níveis)
- `UpdateMenuRequest`: idem + bloqueia item sendo pai de si mesmo

---

## 7. MenuController.php

Herda de `App\Http\Controllers\Controller` (não `Illuminate\Routing\Controller`).

| Método | Rota | Retorno |
|---|---|---|
| `index()` | GET /core/menus | Inertia `Core/Menu/Index` com `arvoreCompleta` |
| `create()` | GET /core/menus/create | Inertia `Core/Menu/Form` com `pais` e `menu: null` |
| `store()` | POST /core/menus | Redirect → index com flash `success` |
| `edit(Menu)` | GET /core/menus/{menu}/edit | Inertia `Core/Menu/Form` com `pais` e `menu` |
| `update(Menu)` | PUT /core/menus/{menu} | Redirect → index com flash `success` |
| `destroy(Menu)` | DELETE /core/menus/{menu} | Redirect → index com flash `success` ou `error` |
| `reordenar()` | PATCH /core/menus/reordenar | `response()->json()` — **não é Inertia** |

---

## 8. Rotas — `Modules/Core/routes/web.php`

```php
Route::middleware(['auth', 'permission:core.menu.gerenciar'])
     ->prefix('core')
     ->name('core.')
     ->group(function () {
         // IMPORTANTE: reordenar antes do resource para não ser capturado por {menu}
         Route::patch('menus/reordenar', [MenuController::class, 'reordenar'])
              ->name('menus.reordenar');
         Route::resource('menus', MenuController::class)->except(['show']);
     });
```

---

## 9. HandleInertiaRequests.php

```php
public function __construct(private MenuService $menuService) {}
// NÃO chamar parent::__construct(Request) — Inertia\Middleware não tem esse construtor
```

`share()` usa:
```php
'menu' => $user ? $this->menuService->arvoreParaUsuario($user) : null,
```

---

## 10. AppSidebar.vue — correções aplicadas

Problemas corrigidos durante a implementação:

1. **`defineProps` sem armazenar retorno** — `collapsed` só ficava disponível no template, não em funções JS. Corrigido com `const props = defineProps(...)`.

2. **Click no botão pai borbulhava para `<aside>`** — o aside tinha `@click` handler próprio que conflitava. Corrigido com `@click.stop` no botão + handler explícito `handlePaiClick`.

3. **Dois cliques para abrir submenu quando colapsado** — primeiro clique expandia o sidebar, segundo clique abria o submenu. Corrigido: `handlePaiClick` expande E já define `grupoAberto` em uma só ação.

4. **`rota` nula causava `route(null)`** — guard adicionado: `:href="!item.rota || item.rota === '#' ? '#' : route(item.rota)"`.

---

## 11. IconPicker.vue

- Dialog PrimeVue com grid 8 colunas, ~62 ícones PrimeIcons
- Filtro em tempo real por nome
- Selecionado: borda teal + fundo teal-50
- Clique seleciona, botão "Confirmar" fecha e emite `update:modelValue`

---

## 12. MenuTree.vue — ajustes aplicados

**Problema 1 — vue-draggable-plus usa slot padrão com `v-for`, não `#item`**  
API correta:
```vue
<VueDraggable v-model="lista">
  <div v-for="item in lista" :key="item.id">...</div>
</VueDraggable>
```

**Problema 2 — handles com mesma classe em listas aninhadas**  
Raiz usa `handle=".drag-root"` e filhos usam `handle=".drag-child"`. Sem isso o SortableJS das instâncias aninhadas conflitava e o arrasto não iniciava.

**Problema 3 — group não configurado em listas aninhadas**  
Adicionado `group="{ name: 'raiz', pull: false, put: false }"` e `group="{ name: 'filhos-{id}', pull: false, put: false }"` para isolar cada lista.

**Problema 4 — `<i>` como handle**  
Trocado para `<span>` com `user-select: none; touch-action: none` e `pointer-events: none` no `<i>` interno.

**Ícone de drag**: `pi pi-th-large` (alterado de `pi-grip-vertical` a pedido).

---

## 13. Index.vue — endpoint de reordenação

**Problema**: `router.patch()` do Inertia espera resposta com header `X-Inertia`. O endpoint `reordenar` retorna `response()->json()` (sem esse header), causando erro "All Inertia requests must receive a valid Inertia response".

**Solução**: usar `axios.patch()` direto. O `bootstrap.js` do Laravel já configura o CSRF token globalmente no axios.

```js
// ❌ Errado — Inertia intercepta e espera resposta Inertia
router.patch(route('core.menus.reordenar'), { itens }, { ... })

// ✅ Correto — axios bypass Inertia, recebe JSON puro
await axios.patch(route('core.menus.reordenar'), { itens })
```

---

## 14. CoreSeeder — dados iniciais

- Permission `core.menu.gerenciar` criada e atribuída ao role `admin`
- 7 itens raiz + 15 filhos inseridos via `firstOrCreate`

Rodar: `php artisan db:seed --class="Modules\\Core\\Database\\Seeders\\CoreSeeder"`

---

## Verificação final

- [x] Migration criada e rodada sem erros
- [x] Seeder popula itens de menu no banco
- [x] `GET /core/menus` bloqueado para usuário sem `core.menu.gerenciar`
- [x] Admin consegue acessar a tela de menu
- [x] Menu do sidebar vem do banco (HandleInertiaRequests → MenuService)
- [x] Submenu do sidebar abre/fecha com um clique
- [x] Drag-and-drop reordena e salva via axios (toast de sucesso/erro)
- [x] Tentativa de criar 3 níveis retorna erro de validação (backend)
- [x] Item não pode ser pai de si mesmo (UpdateMenuRequest)
- [x] Deletar item com filhos ativos retorna erro amigável
- [x] IconPicker filtra ícones e confirma seleção
- [x] Toggle ativo funciona inline na lista (PATCH via router)
- [x] PATCH `/core/menus/reordenar` com IDs inválidos retorna 422
