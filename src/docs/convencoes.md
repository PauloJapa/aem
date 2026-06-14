# Convenções de Nomenclatura

## Banco de dados

| Elemento | Convenção | Exemplos |
|---|---|---|
| Tabelas | snake_case, plural | `pedidos_venda`, `contas_pagar`, `itens_pedido` |
| Colunas FK | `{tabela_singular}_id` | `fornecedor_id`, `pedido_venda_id` |
| Colunas booleanas | prefixo `is_` ou `tem_` | `is_ativo`, `tem_aprovacao` |
| Timestamps | padrão Laravel | `created_at`, `updated_at`, `deleted_at` |

---

## PHP / Laravel

| Elemento | Convenção | Exemplos |
|---|---|---|
| Models | PascalCase, singular | `PedidoVenda`, `ContaPagar`, `ItemPedido` |
| Controllers | PascalCase + Controller | `PedidoVendaController` |
| Services | PascalCase + Service | `PedidoVendaService`, `ParametroService` |
| Repositories | PascalCase + Repository | `PedidoVendaRepository` |
| Interfaces | PascalCase + RepositoryInterface | `PedidoVendaRepositoryInterface` |
| Jobs | Verbo + Substantivo + Job | `GerarPdfPedidoJob`, `EnviarEmailAprovacaoJob` |
| Form Requests | Store/Update/Index + NomeModel + Request | `StoreContaPagarRequest` |
| Policies | NomeModel + Policy | `PedidoVendaPolicy` |
| Exceptions | Descritivo + Exception | `AprovacaoNecessariaException`, `EstoqueInsuficienteException` |

---

## Rotas e permissões

```
# Padrão: modulo.recurso.acao
vendas.pedidos.index
vendas.pedidos.show
vendas.pedidos.criar
vendas.pedidos.editar
vendas.pedidos.excluir
vendas.pedidos.aprovar          # ações específicas de negócio
financeiro.contas-pagar.baixar  # kebab-case para recursos compostos
```

Rotas nomeadas seguem o mesmo padrão:
```php
Route::resource('pedidos', PedidoVendaController::class)
    ->names('vendas.pedidos');
```

---

## Vue / Frontend

| Elemento | Convenção | Exemplos |
|---|---|---|
| Componentes Vue | PascalCase | `PedidoForm.vue`, `ContaPagarIndex.vue` |
| Páginas Inertia | PascalCase por contexto | `Index.vue`, `Show.vue`, `Form.vue` |
| Pinia stores | camelCase + Store | `useVendasStore`, `useFinanceiroStore` |
| Composables | use + Nome | `usePedido`, `useFormatCurrency` |
| Eventos | kebab-case | `@update:model-value`, `@pedido-aprovado` |
| Props | camelCase | `clienteId`, `valorTotal` |

---

## Estrutura de pastas por módulo

```
Modules/NomeModulo/
├── Http/
│   ├── Controllers/        # NomeController.php
│   └── Requests/           # Store/Update + Nome + Request.php
├── Models/                 # NomeSingular.php
├── Services/               # NomeService.php
├── Repositories/           # NomeRepository.php + Interface
├── Jobs/                   # VerbNomeJob.php
├── Policies/               # NomePolicy.php
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   └── seeders/            # NomeModuloPermissionsSeeder.php
├── resources/js/Pages/     # Index.vue, Show.vue, Form.vue
└── tests/
    ├── Unit/
    └── Feature/
```

---

## Commits (Conventional Commits)

```
feat(financeiro): adiciona baixa de conta a pagar
fix(vendas): corrige cálculo de desconto em cascata
refactor(core): extrai lógica de permissão para trait
test(estoque): adiciona teste de movimentação negativa
chore: atualiza dependências do composer
```

Escopos: `core`, `cadastro`, `vendas`, `compras`, `financeiro`, `estoque`, `dre`, `infra`
