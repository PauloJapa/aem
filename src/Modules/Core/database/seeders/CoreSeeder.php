<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir que o cache de permissões está limpo
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

        // ── Atribuir permissions às roles ─────────────────────────────────────
        $admin->syncPermissions($permissions);

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

        $this->command->info('CoreSeeder executado: 3 roles, 13 permissions, 3 usuários.');
    }
}
