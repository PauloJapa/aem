<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Core\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Roles ─────────────────────────────────────────────────────────────
        $admin    = Role::firstOrCreate(['name' => 'admin']);
        $gerente  = Role::firstOrCreate(['name' => 'gerente']);
        $operador = Role::firstOrCreate(['name' => 'operador']);

        // ── Permissions ───────────────────────────────────────────────────────
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

        Permission::firstOrCreate(['name' => 'core.menu.gerenciar']);

        // ── Atribuir permissions às roles ─────────────────────────────────────
        $admin->syncPermissions(array_merge($permissions, ['core.menu.gerenciar']));

        $gerente->syncPermissions(array_values(array_filter(
            $permissions,
            fn ($p) => ! str_starts_with($p, 'dre') && ! str_starts_with($p, 'compras')
        )));

        $operador->syncPermissions(array_values(array_filter(
            $permissions,
            fn ($p) => str_starts_with($p, 'cadastro') || str_starts_with($p, 'vendas')
        )));

        // ── Usuários de teste ─────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@erp.dev'],
            ['name' => 'Administrador', 'password' => bcrypt('password')]
        );
        $adminUser->syncRoles('admin');

        $gerenteUser = User::firstOrCreate(
            ['email' => 'gerente@erp.dev'],
            ['name' => 'Gerente Teste', 'password' => bcrypt('password')]
        );
        $gerenteUser->syncRoles('gerente');

        $operadorUser = User::firstOrCreate(
            ['email' => 'operador@erp.dev'],
            ['name' => 'Operador Teste', 'password' => bcrypt('password')]
        );
        $operadorUser->syncRoles('operador');

        // ── Itens de menu no banco ────────────────────────────────────────────
        $menuItens = [
            ['label' => 'Dashboard',  'icon' => 'pi pi-home',          'rota' => 'dashboard', 'permission' => null,             'ordem' => 1],
            ['label' => 'Cadastro',   'icon' => 'pi pi-database',      'rota' => null,        'permission' => null,             'ordem' => 2],
            ['label' => 'Vendas',     'icon' => 'pi pi-shopping-cart', 'rota' => null,        'permission' => null,             'ordem' => 3],
            ['label' => 'Compras',    'icon' => 'pi pi-shopping-bag',  'rota' => null,        'permission' => null,             'ordem' => 4],
            ['label' => 'Financeiro', 'icon' => 'pi pi-wallet',        'rota' => null,        'permission' => null,             'ordem' => 5],
            ['label' => 'Estoque',    'icon' => 'pi pi-warehouse',     'rota' => null,        'permission' => null,             'ordem' => 6],
            ['label' => 'DRE',        'icon' => 'pi pi-chart-bar',     'rota' => '#',         'permission' => 'dre.visualizar', 'ordem' => 7],
        ];

        $filhos = [
            'Cadastro' => [
                ['label' => 'Clientes',     'icon' => 'pi pi-users', 'rota' => '#', 'permission' => 'cadastro.clientes.visualizar',     'ordem' => 1],
                ['label' => 'Fornecedores', 'icon' => 'pi pi-truck', 'rota' => '#', 'permission' => 'cadastro.fornecedores.visualizar', 'ordem' => 2],
                ['label' => 'Produtos',     'icon' => 'pi pi-box',   'rota' => '#', 'permission' => 'cadastro.produtos.visualizar',     'ordem' => 3],
            ],
            'Vendas' => [
                ['label' => 'Pedidos',    'icon' => 'pi pi-list', 'rota' => '#', 'permission' => 'vendas.pedidos.visualizar',    'ordem' => 1],
                ['label' => 'Orçamentos', 'icon' => 'pi pi-file', 'rota' => '#', 'permission' => 'vendas.orcamentos.visualizar', 'ordem' => 2],
            ],
            'Compras' => [
                ['label' => 'Pedidos de Compra', 'icon' => 'pi pi-list',  'rota' => '#', 'permission' => 'compras.pedidos.visualizar',     'ordem' => 1],
                ['label' => 'Recebimento',       'icon' => 'pi pi-inbox', 'rota' => '#', 'permission' => 'compras.recebimento.visualizar', 'ordem' => 2],
            ],
            'Financeiro' => [
                ['label' => 'Contas a Pagar',   'icon' => 'pi pi-arrow-up-right',  'rota' => '#', 'permission' => 'financeiro.contas-pagar.visualizar',   'ordem' => 1],
                ['label' => 'Contas a Receber', 'icon' => 'pi pi-arrow-down-left', 'rota' => '#', 'permission' => 'financeiro.contas-receber.visualizar', 'ordem' => 2],
                ['label' => 'Fluxo de Caixa',   'icon' => 'pi pi-chart-line',      'rota' => '#', 'permission' => 'financeiro.fluxo-caixa.visualizar',    'ordem' => 3],
            ],
            'Estoque' => [
                ['label' => 'Movimentações', 'icon' => 'pi pi-arrows-v',  'rota' => '#', 'permission' => 'estoque.movimentacoes.visualizar', 'ordem' => 1],
                ['label' => 'Inventário',    'icon' => 'pi pi-clipboard', 'rota' => '#', 'permission' => 'estoque.inventario.visualizar',    'ordem' => 2],
            ],
        ];

        foreach ($menuItens as $item) {
            $pai = Menu::firstOrCreate(
                ['label' => $item['label'], 'parent_id' => null],
                $item
            );
            if (isset($filhos[$item['label']])) {
                foreach ($filhos[$item['label']] as $filho) {
                    Menu::firstOrCreate(
                        ['label' => $filho['label'], 'parent_id' => $pai->id],
                        array_merge($filho, ['parent_id' => $pai->id])
                    );
                }
            }
        }

        $this->command->info('CoreSeeder executado: roles, permissions, usuários e menu populados.');
    }
}
