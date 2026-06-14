# Arquitetura do Sistema

## Visão geral

```
Browser → Nginx (8080) → PHP-FPM (app) → PostgreSQL
                                        → Redis (cache, sessão, filas)
                                        → Horizon (workers)
```

A aplicação usa **Inertia.js** como bridge entre o backend Laravel e o frontend Vue 3. Não há API REST separada para o frontend — o Controller retorna uma resposta Inertia que hidrata o componente Vue diretamente.

---

## Camadas da aplicação

### Request → Response

```
Request HTTP
  └─ Middleware (auth, permission, etc.)
      └─ Controller (thin — só recebe e delega)
          └─ Form Request (validação de entrada)
          └─ Service (regra de negócio)
              └─ Repository (acesso a dados)
              └─ Job (operações assíncronas)
          └─ Inertia::render() ou redirect()
```

### Módulos (nwidart/laravel-modules)

Cada domínio de negócio é um módulo isolado em `src/Modules/`:

```
Modules/
├── Core/          # Auth, usuários, permissões, parâmetros, auditoria
├── Cadastro/      # Clientes, fornecedores, produtos, categorias
├── Vendas/        # Pedidos, orçamentos, NF-e
├── Compras/       # Pedidos de compra, recebimento
├── Financeiro/    # Contas a pagar/receber, fluxo de caixa
├── Estoque/       # Movimentações, saldos, inventário
└── DRE/           # Demonstrativo de resultado
```

Cada módulo tem sua própria estrutura completa (Controllers, Models, Services, Repositories, migrations, rotas, testes, páginas Vue).

---

## Controle de acesso (3 camadas)

**Camada 1 — Rotas/Telas** (Spatie Permission)
```
Usuário → Roles → Permissions → middleware permission:modulo.recurso.acao
```

**Camada 2 — Escopo de dados** (Eloquent Policies + Global Scopes)
```
Policy::authorize() antes de retornar dados sensíveis
Global Scope filtra automaticamente por contexto do usuário
```

**Camada 3 — Regras parametrizadas** (tabela `parametros`)
```
Service consulta parametros antes de executar ações críticas
Ex: limite de aprovação, desconto máximo, prazo de parcelamento
```

---

## Filas (Horizon + Redis)

Operações pesadas **nunca bloqueiam** o request HTTP — sempre vão para fila:

| Fila | Uso |
|---|---|
| `default` | Tarefas gerais |
| `notifications` | Emails e alertas |
| `documents` | Geração de PDFs |
| `reports` | DRE e relatórios pesados |

```php
EnviarEmailPedidoJob::dispatch($pedido)->onQueue('notifications');
GerarPdfNotaFiscalJob::dispatch($pedido)->onQueue('documents');
```

---

## Frontend (Vue 3 + Inertia)

```
resources/js/
├── app.js              # Entry point — inicializa Inertia + Vue
├── Components/         # Componentes globais reutilizáveis
├── Layouts/            # AppLayout, AuthLayout
└── Pages/              # Páginas globais (Login, Dashboard)

Modules/NomeModulo/
└── resources/js/Pages/ # Páginas do módulo (roteadas pelo Inertia)
```

O Vite agrupa automaticamente as páginas dos módulos via configuração em `vite.config.js`.

Estado global via **Pinia** (`useNomeStore`).
Formulários validados com **VeeValidate + Zod**.
Componentes UI via **PrimeVue 4**.

---

## Auditoria

Todos os models críticos usam `LogsActivity` (Spatie Activitylog).
Os logs ficam em `activity_log` com diff de campos antes/depois de cada alteração.

```php
use Spatie\Activitylog\Traits\LogsActivity;

class ContaPagar extends Model
{
    use LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
}
```
