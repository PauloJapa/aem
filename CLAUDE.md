# ERP — Contexto do Projeto para o Claude Code

Este arquivo é lido automaticamente pelo Claude Code a cada sessão.
Mantenha-o atualizado sempre que houver mudanças relevantes na arquitetura.

---

## Visão Geral

ERP web modular implantado em **VPS por cliente** (não é SaaS).
Cada instalação serve uma empresa isolada. Não há multi-tenancy.

---

## Stack Tecnológica

### Backend
| Componente | Tecnologia | Versão |
|---|---|---|
| Framework | Laravel | 12.x |
| PHP | PHP-FPM | 8.3 |
| Banco de dados | PostgreSQL | 16 |
| Cache / Filas | Redis | 7.2 |
| Worker de filas | Laravel Horizon | latest |
| Scheduler | Laravel Schedule | nativo |
| Módulos | nwidart/laravel-modules | latest |
| Permissões | spatie/laravel-permission | latest |
| Auditoria | spatie/laravel-activitylog | latest |
| PDF | barryvdh/laravel-dompdf | latest |
| Debug (dev) | laravel/telescope | latest |

### Frontend
| Componente | Tecnologia |
|---|---|
| Bridge SPA | Inertia.js |
| Framework JS | Vue 3 (Composition API) |
| Build tool | Vite |
| UI Components | PrimeVue 4 |
| Estilos | Tailwind CSS |
| Estado global | Pinia |
| Validação forms | VeeValidate + Zod |

### Infraestrutura (Docker)
| Serviço | Container | Porta local |
|---|---|---|
| App (PHP-FPM) | erp_app | — |
| Web (Nginx) | erp_nginx | 8080 |
| Banco | erp_postgres | 5432 |
| Cache/Fila | erp_redis | 6379 |
| Worker | erp_horizon | — |
| Scheduler | erp_scheduler | — |
| Frontend dev | erp_vite | 5173 |
| Admin BD | erp_pgadmin | 5050 |
| Email dev | erp_mailpit | 8025 (UI), 1025 (SMTP) |

---

## Estrutura de Pastas

```
/
├── docker/                     # Configs Docker
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── nginx/default.conf
│   ├── postgres/init.sql
│   └── pgadmin/servers.json
│
├── src/                        # Código Laravel (raiz do projeto)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # Thin — só recebe, delega, responde
│   │   │   ├── Middleware/
│   │   │   └── Requests/       # Form Requests com validação
│   │   ├── Models/             # Modelos globais (User, etc.)
│   │   ├── Services/           # Regras de negócio globais
│   │   ├── Repositories/       # Acesso a dados (interface + implementação)
│   │   ├── Policies/           # Autorização por modelo
│   │   ├── Jobs/               # Jobs globais (email, pdf, notificações)
│   │   ├── Notifications/
│   │   └── Support/            # Helpers, Traits, ValueObjects
│   │
│   ├── Modules/                # Módulos ERP (nwidart/laravel-modules)
│   │   ├── Core/               # Auth, permissões, parâmetros, auditoria
│   │   ├── Cadastro/           # Clientes, fornecedores, produtos, categorias
│   │   ├── Vendas/             # Pedidos, orçamentos, NF-e
│   │   ├── Compras/            # Pedidos de compra, recebimento
│   │   ├── Financeiro/         # Contas a pagar/receber, fluxo de caixa
│   │   ├── Estoque/            # Movimentações, saldos, inventário
│   │   └── DRE/                # Demonstrativo de resultado
│   │
│   │   # Estrutura interna de cada módulo:
│   │   # Modules/NomeModulo/
│   │   # ├── Http/Controllers/
│   │   # ├── Http/Requests/
│   │   # ├── Models/
│   │   # ├── Services/
│   │   # ├── Repositories/
│   │   # ├── Jobs/
│   │   # ├── Policies/
│   │   # ├── routes/
│   │   # │   ├── web.php
│   │   # │   └── api.php
│   │   # ├── database/
│   │   # │   ├── migrations/
│   │   # │   └── seeders/
│   │   # ├── resources/
│   │   # │   └── js/Pages/    # Páginas Vue/Inertia do módulo
│   │   # └── tests/
│   │   #     ├── Unit/
│   │   #     └── Feature/
│   │
│   ├── database/
│   │   ├── migrations/         # Migrations globais (users, etc.)
│   │   └── seeders/
│   │
│   ├── resources/
│   │   ├── js/
│   │   │   ├── app.js          # Entry point Inertia + Vue
│   │   │   ├── bootstrap.js
│   │   │   ├── Components/     # Componentes Vue reutilizáveis globais
│   │   │   ├── Layouts/        # Layouts base (AppLayout, AuthLayout)
│   │   │   └── Pages/          # Páginas globais (Login, Dashboard)
│   │   └── views/
│   │       └── app.blade.php   # Shell HTML do Inertia
│   │
│   └── tests/
│       ├── Unit/
│       ├── Feature/
│       └── Pest.php
│
├── docker-compose.yml
├── .env.example
└── CLAUDE.md
```

---

## Controle de Acesso — Modelo de 3 Camadas

### Camada 1 — Acesso a telas/rotas (Spatie Permission)
```
Usuário → Roles → Permissions → Rotas / Controllers
```
- Cada permissão mapeia para uma rota ou grupo de rotas
- Verificação via middleware `permission:nome.da.permissao`
- Nomenclatura: `modulo.recurso.acao` → ex: `financeiro.contas-pagar.visualizar`

### Camada 2 — Escopo de dados (Eloquent Policies + Global Scopes)
```php
// Exemplos de escopo:
// - Vendedor vê apenas seus próprios pedidos
// - Gerente vê pedidos da sua filial
// - Admin vê tudo
```
- Sempre usar Policy antes de retornar dados sensíveis
- Global Scopes para filtragem automática por contexto do usuário

### Camada 3 — Regras de negócio parametrizadas (tabela `parametros`)
```
Tabela: parametros
- chave: string único (ex: 'vendas.aprovacao.limite_valor')
- valor: string (cast conforme necessidade)
- modulo: string
- descricao: string
```
- Services consultam parâmetros antes de executar ações críticas
- Tela de configuração no módulo Core permite ajuste por usuário admin
- Exemplos: limite de aprovação, prazo máximo de parcelamento, desconto máximo

---

## Padrões de Código

### Controllers — thin, sempre
```php
// ✅ Correto
public function store(StorePedidoRequest $request, PedidoService $service)
{
    $pedido = $service->criar($request->validated());
    return redirect()->route('vendas.pedidos.show', $pedido);
}

// ❌ Errado — regra de negócio no controller
public function store(Request $request)
{
    if ($request->valor > 5000 && !auth()->user()->can('aprovar-pedido-alto-valor')) {
        abort(403);
    }
    // ...lógica no controller
}
```

### Services — onde mora a regra de negócio
```php
class PedidoService
{
    public function __construct(
        private PedidoRepository $pedidos,
        private ParametroService $parametros,
    ) {}

    public function criar(array $dados): Pedido
    {
        // 1. valida regras de negócio
        // 2. consulta parâmetros se necessário
        // 3. delega persistência ao repository
        // 4. dispara jobs (email, notificação, etc.)
    }
}
```

### Filas — obrigatório para operações pesadas
Sempre usar Jobs para:
- Envio de email
- Geração de PDF
- Notificações WhatsApp (futuro)
- Relatórios e exportações
- Sincronizações externas

```php
// Dispatchar de forma assíncrona
EnviarEmailPedidoJob::dispatch($pedido)->onQueue('notifications');
GerarPdfNotaFiscalJob::dispatch($pedido)->onQueue('documents');
```

Filas disponíveis (configurar no Horizon):
- `default` — tarefas gerais
- `notifications` — emails e alertas
- `documents` — geração de PDFs e relatórios
- `reports` — DRE e relatórios pesados (baixa prioridade)

### Repositories — isolar acesso a dados
```php
interface PedidoRepositoryInterface
{
    public function criar(array $dados): Pedido;
    public function buscarPorId(int $id): Pedido;
    public function listarPorFiltros(array $filtros): LengthAwarePaginator;
}

class PedidoRepository implements PedidoRepositoryInterface
{
    // Implementação com Eloquent
}
```

### Auditoria — automática via Spatie Activitylog
- Todos os models principais devem usar a trait `LogsActivity`
- Configurar `$recordEvents` e `getActivitylogOptions()` em cada model
- Logs ficam na tabela `activity_log` com diff de campos

---

## Padrões de Teste (Pest PHP)

### Unit — testa Service/lógica isolada
```php
// tests/Unit/Vendas/PedidoServiceTest.php
it('não permite pedido acima do limite sem aprovação', function () {
    $parametros = mock(ParametroService::class)
        ->expect(get: fn($chave) => 5000.00);

    $service = new PedidoService(
        pedidos: mock(PedidoRepository::class),
        parametros: $parametros,
    );

    expect(fn() => $service->criar(['valor' => 6000.00, 'aprovado' => false]))
        ->toThrow(LimiteDeAprovacaoException::class);
});
```

### Feature — testa fluxo HTTP completo
```php
// tests/Feature/Vendas/PedidoTest.php
it('vendedor consegue criar pedido dentro do limite', function () {
    $vendedor = User::factory()->withRole('vendedor')->create();

    actingAs($vendedor)
        ->post(route('vendas.pedidos.store'), [
            'cliente_id' => Cliente::factory()->create()->id,
            'valor'      => 1000.00,
            'itens'      => [...],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});
```

---

## Comandos Úteis no Dia a Dia

```bash
# Entrar no container da aplicação
docker compose exec app bash

# Rodar migrations
docker compose exec app php artisan migrate

# Criar novo módulo
docker compose exec app php artisan module:make NomeModulo

# Rodar testes
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=NomeDoTeste

# Ver filas no Horizon
# Acessar: http://localhost:8080/horizon

# Ver emails capturados (Mailpit)
# Acessar: http://localhost:8025

# Gerenciar banco (pgAdmin)
# Acessar: http://localhost:5050

# Limpar caches
docker compose exec app php artisan optimize:clear

# Gerar chave da aplicação (primeira vez)
docker compose exec app php artisan key:generate
```

---

## Convenções de Nomenclatura

| Elemento | Convenção | Exemplo |
|---|---|---|
| Tabelas | snake_case, plural | `pedidos_venda`, `contas_pagar` |
| Models | PascalCase, singular | `PedidoVenda`, `ContaPagar` |
| Controllers | PascalCase + Controller | `PedidoVendaController` |
| Services | PascalCase + Service | `PedidoVendaService` |
| Jobs | Verbo + Substantivo + Job | `GerarPdfPedidoJob` |
| Permissões | kebab-case com pontos | `vendas.pedidos.aprovar` |
| Rotas | kebab-case com pontos | `vendas.pedidos.store` |
| Views Vue | PascalCase | `PedidoIndex.vue`, `PedidoForm.vue` |
| Pinia stores | camelCase + Store | `useVendasStore` |

---

## Módulos e Responsabilidades

| Módulo | Responsabilidade principal |
|---|---|
| **Core** | Auth, usuários, perfis, permissões, parâmetros, auditoria, logs |
| **Cadastro** | Clientes, fornecedores, produtos, categorias, tabelas de preço |
| **Vendas** | Orçamentos, pedidos, faturamento, NF-e |
| **Compras** | Requisições, pedidos de compra, recebimento de mercadoria |
| **Financeiro** | Contas a pagar, contas a receber, fluxo de caixa, conciliação |
| **Estoque** | Entradas, saídas, transferências, saldos, inventário |
| **DRE** | Demonstrativo de resultado, centros de custo, relatórios gerenciais |

---

## estrutura global para documentação do projeto (para o Claude Code entender o contexto de cada módulo):
src/docs/                    # arquitetura geral
├── modulos.md
├── arquitetura.md
├── controle-acesso.md
├── padroes-codigo.md
├── ambiente.md
└── decisoes/            # ADR — por que escolhemos X
    ├── 001-postgresql.md
    └── 002-inertia-vue.md

Modules/Financeiro/
└── docs/
    ├── spec.md          # o principal — regras, entidades, fluxos
    ├── entidades.md
    ├── fluxos.md
    └── permissoes.md

## Cuidados Especiais

1. **Nunca colocar regra de negócio em Controller ou Model** — use Services
2. **Toda operação pesada vai para fila** — nunca bloqueia o request
3. **Toda ação de escrita deve ter Policy verificada** antes de executar
4. **Migrations são imutáveis** — nunca editar migration existente, sempre criar nova
5. **PostgreSQL** — aproveitar CTEs, window functions e índices trigram para buscas
6. **Testes antes do PR** — `php artisan test` deve passar 100% antes de commitar
7. **Variáveis de ambiente** — nunca hardcodar valores, sempre usar `config()` ou `env()`
