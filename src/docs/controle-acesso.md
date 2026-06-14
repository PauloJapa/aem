# Controle de Acesso

O sistema implementa 3 camadas independentes de controle.

---

## Camada 1 — Acesso a telas e rotas (Spatie Permission)

```
Usuário → [Role: vendedor] → [Permission: vendas.pedidos.criar] → Rota
```

### Definindo permissões num módulo

Criar um seeder no módulo:

```php
// Modules/Vendas/Database/Seeders/VendasPermissionsSeeder.php

use Spatie\Permission\Models\Permission;

$permissions = [
    'vendas.visualizar',
    'vendas.pedidos.index',
    'vendas.pedidos.criar',
    'vendas.pedidos.editar',
    'vendas.pedidos.excluir',
    'vendas.pedidos.aprovar',
    'vendas.orcamentos.index',
    'vendas.orcamentos.criar',
];

foreach ($permissions as $perm) {
    Permission::firstOrCreate(['name' => $perm]);
}
```

### Protegendo rotas

```php
// Modules/Vendas/routes/web.php
Route::middleware(['auth', 'permission:vendas.pedidos.index'])
    ->group(function () {
        Route::get('/vendas/pedidos', [PedidoVendaController::class, 'index'])
             ->name('vendas.pedidos.index');
    });
```

### No Form Request (autorização granular)

```php
public function authorize(): bool
{
    return $this->user()->can('vendas.pedidos.criar');
}
```

---

## Camada 2 — Escopo de dados (Policies + Global Scopes)

### Policy

```php
// Modules/Vendas/Policies/PedidoVendaPolicy.php
class PedidoVendaPolicy
{
    public function view(User $user, PedidoVenda $pedido): bool
    {
        // Admin vê tudo
        if ($user->hasRole('admin')) return true;

        // Gerente vê da sua filial
        if ($user->hasRole('gerente')) {
            return $user->filial_id === $pedido->filial_id;
        }

        // Vendedor vê só os seus
        return $user->id === $pedido->vendedor_id;
    }

    public function aprovar(User $user, PedidoVenda $pedido): bool
    {
        return $user->can('vendas.pedidos.aprovar')
            && $pedido->status === 'pendente';
    }
}
```

Registrar no ServiceProvider do módulo:
```php
Gate::policy(PedidoVenda::class, PedidoVendaPolicy::class);
```

Usar no Service antes de retornar dados:
```php
public function buscar(int $id): PedidoVenda
{
    $pedido = $this->pedidos->buscarPorId($id);
    Gate::authorize('view', $pedido);
    return $pedido;
}
```

### Global Scope (filtro automático)

```php
// Aplicar automaticamente no Model para limitar escopo por usuário
class PedidoVenda extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('escopo', function (Builder $query) {
            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                $query->where('vendedor_id', $user->id);
            }
        });
    }
}
```

---

## Camada 3 — Regras de negócio parametrizadas (tabela `parametros`)

Parâmetros são configurações que admins podem ajustar sem deploy.

### Estrutura da tabela

```
parametros
├── chave       string único   'vendas.pedidos.limite_aprovacao'
├── valor       string         '5000.00'
├── modulo      string         'vendas'
└── descricao   string         'Valor máximo para criação sem aprovação'
```

### Usando no Service

```php
class PedidoVendaService
{
    public function __construct(
        private PedidoVendaRepository $pedidos,
        private ParametroService $parametros,
    ) {}

    public function criar(array $dados): PedidoVenda
    {
        $limite = (float) $this->parametros->get('vendas.pedidos.limite_aprovacao', 5000);

        if ($dados['valor_total'] > $limite && !$dados['aprovado']) {
            throw new AprovacaoNecessariaException(
                "Pedido acima de R$ {$limite} requer aprovação prévia."
            );
        }

        return $this->pedidos->criar($dados);
    }
}
```

### Exemplos de parâmetros por módulo

| Chave | Módulo | Descrição |
|---|---|---|
| `vendas.pedidos.limite_aprovacao` | Vendas | Valor máximo sem aprovação |
| `vendas.desconto.percentual_maximo` | Vendas | Desconto máximo permitido |
| `financeiro.parcelas.prazo_maximo_dias` | Financeiro | Prazo máximo de parcelamento |
| `estoque.movimentacao.permite_negativo` | Estoque | Permite saldo negativo |
| `compras.pedido.aprovacao_obrigatoria` | Compras | Toda compra precisa de aprovação |
