# Tarefa: Tela de Login + Menu Filtrado por Permissões

## Contexto
Stack: Laravel 12 + Inertia.js + Vue 3 + PrimeVue 4 + Spatie Permission  
O `AppLayout.vue` e `AppSidebar.vue` já existem em `resources/js/`  
Módulo Core já criado em `Modules/Core/`

---

## Visão Geral da Arquitetura

```
Login (POST) → AuthController
                    ↓
             Gera sessão
                    ↓
             Redireciona para /dashboard
                    ↓
             HandleInertiaRequests::share()
             injeta auth.user + auth.menu (filtrado por permissions)
                    ↓
             AppSidebar.vue lê menu de $page.props.auth.menu
             e renderiza apenas os itens que o usuário pode ver
```

O menu **nunca** é montado no frontend com lógica de permissão.
O Laravel decide o que envia — o Vue só exibe o que recebe.

---

## Arquivos a criar / modificar

```
Modules/Core/
├── Http/
│   ├── Controllers/
│   │   └── AuthController.php          ← criar
│   └── Requests/
│       └── LoginRequest.php            ← criar
├── routes/
│   └── web.php                         ← modificar

app/Http/Middleware/
└── HandleInertiaRequests.php           ← modificar (já existe)

resources/js/
├── Pages/
│   └── Auth/
│       └── Login.vue                   ← criar
└── Layouts/
    └── GuestLayout.vue                 ← criar (layout sem sidebar para login)
```

---

## 1. `LoginRequest.php`

```php
namespace Modules\Core\Http\Requests;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }

    // Throttle: máximo 5 tentativas por minuto por IP+email
    public function authenticate(): void
    {
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }
        RateLimiter::clear($this->throttleKey());
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
```

---

## 2. `AuthController.php`

```php
namespace Modules\Core\Http\Controllers;

// Métodos necessários:

// showLogin() → retorna Inertia 'Auth/Login' (sem props)

// login(LoginRequest $request)
//   - chama $request->authenticate()
//   - regenera sessão: $request->session()->regenerate()
//   - redireciona para intended('/dashboard')

// logout(Request $request)
//   - Auth::logout()
//   - invalida sessão e regenera token CSRF
//   - redireciona para route('login')
```

---

## 3. Rotas em `Modules/Core/routes/web.php`

```php
// Rotas guest (sem autenticação)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout (autenticado)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
});
```

---

## 4. `HandleInertiaRequests.php` — compartilhar menu filtrado

Modificar o método `share()` para incluir `auth.user` e `auth.menu`:

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user() ? [
                'id'     => $request->user()->id,
                'name'   => $request->user()->name,
                'email'  => $request->user()->email,
                'avatar' => null, // futuro: URL do avatar
            ] : null,

            // Menu filtrado pelas permissions do usuário logado
            // Null se não autenticado
            'menu' => $request->user()
                ? $this->buildMenu($request->user())
                : null,
        ],
        'flash' => [
            'success' => $request->session()->get('success'),
            'error'   => $request->session()->get('error'),
        ],
    ]);
}

private function buildMenu(\App\Models\User $user): array
{
    // Definição completa do menu com guards de permission
    // Cada item tem: label, icon, rota, permission (opcional), filhos (opcional)
    // Um item SÓ aparece se:
    //   - não tem 'permission' definida (item público para autenticados), OU
    //   - $user->can($item['permission']) retorna true
    // Um grupo (com filhos) SÓ aparece se pelo menos 1 filho passar no filtro

    $menu = [
        [
            'label' => 'Dashboard',
            'icon'  => 'pi pi-home',
            'rota'  => 'dashboard',
            // sem permission = visível para todos autenticados
        ],
        [
            'label'  => 'Cadastro',
            'icon'   => 'pi pi-database',
            'filhos' => [
                ['label' => 'Clientes',     'icon' => 'pi pi-users',  'rota' => '#', 'permission' => 'cadastro.clientes.visualizar'],
                ['label' => 'Fornecedores', 'icon' => 'pi pi-truck',  'rota' => '#', 'permission' => 'cadastro.fornecedores.visualizar'],
                ['label' => 'Produtos',     'icon' => 'pi pi-box',    'rota' => '#', 'permission' => 'cadastro.produtos.visualizar'],
            ],
        ],
        [
            'label'  => 'Vendas',
            'icon'   => 'pi pi-shopping-cart',
            'filhos' => [
                ['label' => 'Pedidos',     'icon' => 'pi pi-list', 'rota' => '#', 'permission' => 'vendas.pedidos.visualizar'],
                ['label' => 'Orçamentos',  'icon' => 'pi pi-file', 'rota' => '#', 'permission' => 'vendas.orcamentos.visualizar'],
            ],
        ],
        [
            'label'  => 'Compras',
            'icon'   => 'pi pi-shopping-bag',
            'filhos' => [
                ['label' => 'Pedidos de Compra', 'icon' => 'pi pi-list',  'rota' => '#', 'permission' => 'compras.pedidos.visualizar'],
                ['label' => 'Recebimento',        'icon' => 'pi pi-inbox', 'rota' => '#', 'permission' => 'compras.recebimento.visualizar'],
            ],
        ],
        [
            'label'  => 'Financeiro',
            'icon'   => 'pi pi-wallet',
            'filhos' => [
                ['label' => 'Contas a Pagar',   'icon' => 'pi pi-arrow-up-right',  'rota' => '#', 'permission' => 'financeiro.contas-pagar.visualizar'],
                ['label' => 'Contas a Receber', 'icon' => 'pi pi-arrow-down-left', 'rota' => '#', 'permission' => 'financeiro.contas-receber.visualizar'],
                ['label' => 'Fluxo de Caixa',   'icon' => 'pi pi-chart-line',      'rota' => '#', 'permission' => 'financeiro.fluxo-caixa.visualizar'],
            ],
        ],
        [
            'label'  => 'Estoque',
            'icon'   => 'pi pi-warehouse',
            'filhos' => [
                ['label' => 'Movimentações', 'icon' => 'pi pi-arrows-v',  'rota' => '#', 'permission' => 'estoque.movimentacoes.visualizar'],
                ['label' => 'Inventário',    'icon' => 'pi pi-clipboard', 'rota' => '#', 'permission' => 'estoque.inventario.visualizar'],
            ],
        ],
        [
            'label'      => 'DRE',
            'icon'       => 'pi pi-chart-bar',
            'rota'       => '#',
            'permission' => 'dre.visualizar',
        ],
    ];

    return $this->filtrarMenu($menu, $user);
}

private function filtrarMenu(array $itens, \App\Models\User $user): array
{
    $resultado = [];

    foreach ($itens as $item) {
        // Item com filhos — filtrar os filhos primeiro
        if (isset($item['filhos'])) {
            $filhosFiltrados = $this->filtrarMenu($item['filhos'], $user);
            if (count($filhosFiltrados) > 0) {
                $item['filhos'] = $filhosFiltrados;
                $resultado[] = $item;
            }
            continue;
        }

        // Item simples — verificar permission
        if (! isset($item['permission']) || $user->can($item['permission'])) {
            $resultado[] = $item;
        }
    }

    return $resultado;
}
```

---

## 5. `GuestLayout.vue`

Layout minimalista para telas de auth (login, esqueci senha).  
Sem sidebar, sem header. Fundo com gradiente teal suave.

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│                                                     │
│              ┌─────────────────────┐               │
│              │      [Logo/ERP]     │               │
│              │                     │               │
│              │   <slot />          │               │
│              │                     │               │
│              └─────────────────────┘               │
│                                                     │
│                                                     │
└─────────────────────────────────────────────────────┘
```

- Fundo: gradiente de `teal-900` para `teal-700`
- Card central: branco, sombra, `max-width: 400px`, bordas arredondadas
- Logo: texto "ERP" em teal-600, fonte grande, centralizado
- Responsivo — funciona em mobile

---

## 6. `Login.vue`

Usar `GuestLayout` como layout da página.

```
┌─────────────────────┐
│        ERP          │
│   Sistema de Gestão │
├─────────────────────┤
│ E-mail              │
│ [________________]  │
│                     │
│ Senha               │
│ [________________]  │
│                     │
│ ☐ Lembrar-me        │
│                     │
│ [  Entrar  ]        │
│                     │
│  Esqueci minha senha│
└─────────────────────┘
```

Componentes PrimeVue:
- `InputText` para email
- `Password` para senha (com toggle mostrar/ocultar, sem medidor de força)
- `Checkbox` para lembrar-me
- `Button` primário para entrar (com `loading` enquanto submete)
- Link simples para "Esqueci minha senha" (rota `#` por enquanto)

Comportamento:
- Usar `useForm` do Inertia (`@inertiajs/vue3`) para gerenciar o form
- Exibir erros de validação abaixo de cada campo usando `form.errors.campo`
- Botão fica em loading durante o POST (`form.processing`)
- Ao pressionar Enter no campo senha, submete o form
- Após login bem-sucedido o Laravel redireciona — não precisa lógica no Vue

---

## 7. Atualizar `AppSidebar.vue`

Remover o array `menu` hardcoded que foi criado na tarefa anterior.  
Substituir por:

```js
import { usePage } from '@inertiajs/vue3'
const menu = computed(() => usePage().props.auth.menu ?? [])
```

O restante do componente (renderização, submenu, collapsed) permanece igual.  
Agora o menu vem do Laravel filtrado — o componente só renderiza o que recebe.

---

## Seeder de usuário e permissões para teste

Criar `Modules/Core/Database/Seeders/CoreSeeder.php`:

```php
// Criar roles base
$admin    = Role::firstOrCreate(['name' => 'admin']);
$gerente  = Role::firstOrCreate(['name' => 'gerente']);
$operador = Role::firstOrCreate(['name' => 'operador']);

// Criar permissions de todos os módulos
$permissions = [
    'cadastro.clientes.visualizar',
    'cadastro.fornecedores.visualizar',
    'cadastro.produtos.visualizar',
    'vendas.pedidos.visualizar',
    'vendas.orcamentos.visualizar',
    'compras.pedidos.visualizar',
    'compras.recebimento.visualizar',
    'financeiro.contas-pagar.visualizar',
    'financeiro.contas-receber.visualizar',
    'financeiro.fluxo-caixa.visualizar',
    'estoque.movimentacoes.visualizar',
    'estoque.inventario.visualizar',
    'dre.visualizar',
];

foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

// Admin tem tudo
$admin->syncPermissions($permissions);

// Gerente — sem DRE e sem compras
$gerente->syncPermissions(array_filter($permissions,
    fn($p) => ! str_starts_with($p, 'dre') && ! str_starts_with($p, 'compras')
));

// Operador — só cadastro e vendas
$operador->syncPermissions(array_filter($permissions,
    fn($p) => str_starts_with($p, 'cadastro') || str_starts_with($p, 'vendas')
));

// Criar usuários de teste
$admin_user = User::firstOrCreate(
    ['email' => 'admin@erp.dev'],
    ['name' => 'Administrador', 'password' => bcrypt('password')]
);
$admin_user->assignRole('admin');

$gerente_user = User::firstOrCreate(
    ['email' => 'gerente@erp.dev'],
    ['name' => 'Gerente Teste', 'password' => bcrypt('password')]
);
$gerente_user->assignRole('gerente');

$operador_user = User::firstOrCreate(
    ['email' => 'operador@erp.dev'],
    ['name' => 'Operador Teste', 'password' => bcrypt('password')]
);
$operador_user->assignRole('operador');
```

Rodar com:
```bash
php artisan db:seed --class=Modules\\Core\\Database\\Seeders\\CoreSeeder
```

---

## Ordem de execução

1. Criar `LoginRequest.php`
2. Criar `AuthController.php`
3. Registrar rotas em `Modules/Core/routes/web.php`
4. Modificar `HandleInertiaRequests.php` — adicionar `buildMenu()` e `filtrarMenu()`
5. Criar `GuestLayout.vue`
6. Criar `Login.vue`
7. Atualizar `AppSidebar.vue` — trocar menu hardcoded por `usePage().props.auth.menu`
8. Criar e rodar `CoreSeeder`
9. Testar com os 3 usuários — verificar que cada um vê menus diferentes

---

## Verificação final

- [ ] `GET /login` abre a tela de login com o layout correto
- [ ] Credenciais erradas exibem erro abaixo do campo email
- [ ] Após 5 tentativas erradas, próxima tentativa retorna erro de rate limit
- [ ] Login com `admin@erp.dev` → menu completo (todos os módulos)
- [ ] Login com `gerente@erp.dev` → menu sem DRE e sem Compras
- [ ] Login com `operador@erp.dev` → menu só com Cadastro e Vendas
- [ ] Checkbox "Lembrar-me" funciona (sessão persiste ao fechar browser)
- [ ] Botão "Sair" faz POST para `/logout` e volta para `/login`
- [ ] Rota `/dashboard` sem autenticação redireciona para `/login`

## Tudo concluído. Resumo do que foi feito:

- Backend (PHP)
- 
- Arquivo	O que faz
- LoginRequest.php	Validação + autenticação com rate limit (5 tentativas/min)
- AuthController.php	showLogin, login, logout
- Core/routes/web.php	Rotas guest (login) e auth (logout, dashboard)
- routes/web.php	Simplificado — redireciona / → dashboard
- HandleInertiaRequests.php	Compartilha auth.user, auth.menu filtrado e flash
- User.php	Adicionada trait HasRoles do Spatie
- CoreSeeder.php	3 roles, 13 permissions, 3 usuários de teste
- Frontend (Vue)
- 
- Arquivo	O que faz
- GuestLayout.vue	Layout com gradiente teal para telas de auth
- Login.vue	Formulário com InputText, Password, Checkbox, loading
- AppSidebar.vue	Menu agora vem de auth.menu do backend
- Usuários para teste:
- 
- admin@erp.dev / password → menu completo
- gerente@erp.dev / password → sem DRE e sem Compras
- operador@erp.dev / password → só Cadastro e Vendas