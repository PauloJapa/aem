# O fluxo completo em ordem:

## 1. Criar o módulo
bashdocker compose exec app php artisan module:make Financeiro
## 2. Gerar a documentação
bashdocker compose exec app php artisan module:spec Financeiro
## 3. Preencher o spec.md antes de codar
Abrir src/Modules/Financeiro/docs/spec.md e preencher pelo menos:

## Objetivo — o que o módulo resolve
Entidades — quais tabelas/models vai ter
Regras de Negócio — as RNs que viram testes
Permissões — a lista de permissions do módulo
Isso antes de escrever qualquer código — o spec vira o contrato do que será desenvolvido.

## 4. Criar as migrations
bashdocker compose exec app php artisan module:make-migration create_contas_pagar_table Financeiro
docker compose exec app php artisan module:make-migration create_contas_receber_table Financeiro
## 5. Criar os Models
bashdocker compose exec app php artisan module:make-model ContaPagar Financeiro
docker compose exec app php artisan module:make-model ContaReceber Financeiro
## 6. Rodar as migrations
bashdocker compose exec app php artisan module:migrate Financeiro
## 7. Criar Service, Repository e Controller
bashdocker compose exec app php artisan module:make-service ContaPagarService Financeiro
docker compose exec app php artisan module:make-repository ContaPagarRepository Financeiro
docker compose exec app php artisan module:make-controller ContaPagarController Financeiro
## 8. Registrar as permissões no seeder
Em src/Modules/Financeiro/Database/Seeders/FinanceiroPermissionsSeeder.php:
php$permissions = [
    'financeiro.visualizar',
    'financeiro.contas-pagar.visualizar',
    'financeiro.contas-pagar.criar',
    'financeiro.contas-pagar.editar',
    'financeiro.contas-pagar.excluir',
    'financeiro.contas-receber.visualizar',
    // ...
];

foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission]);
}
## 9. Commit
bashgit add .
git commit -m "feat(financeiro): estrutura inicial do módulo"

Resumo do fluxo:
module:make → module:spec → preencher spec → migrations → 
models → migrate → service/repository/controller → permissions → commit
A regra de ouro é: spec preenchido antes do primeiro model. Se você não consegue preencher o objetivo e as entidades, o módulo ainda não está maduro o suficiente para começar a codar.