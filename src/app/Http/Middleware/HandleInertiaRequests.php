<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'avatar' => null,
                ] : null,
                'menu' => $user ? $this->buildMenu($user) : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ]);
    }

    private function buildMenu(User $user): array
    {
        $menu = [
            [
                'label' => 'Dashboard',
                'icon'  => 'pi pi-home',
                'rota'  => 'dashboard',
            ],
            [
                'label'  => 'Cadastro',
                'icon'   => 'pi pi-database',
                'filhos' => [
                    ['label' => 'Clientes',     'icon' => 'pi pi-users', 'rota' => '#', 'permission' => 'cadastro.clientes.visualizar'],
                    ['label' => 'Fornecedores', 'icon' => 'pi pi-truck', 'rota' => '#', 'permission' => 'cadastro.fornecedores.visualizar'],
                    ['label' => 'Produtos',     'icon' => 'pi pi-box',   'rota' => '#', 'permission' => 'cadastro.produtos.visualizar'],
                ],
            ],
            [
                'label'  => 'Vendas',
                'icon'   => 'pi pi-shopping-cart',
                'filhos' => [
                    ['label' => 'Pedidos',    'icon' => 'pi pi-list', 'rota' => '#', 'permission' => 'vendas.pedidos.visualizar'],
                    ['label' => 'Orçamentos', 'icon' => 'pi pi-file', 'rota' => '#', 'permission' => 'vendas.orcamentos.visualizar'],
                ],
            ],
            [
                'label'  => 'Compras',
                'icon'   => 'pi pi-shopping-bag',
                'filhos' => [
                    ['label' => 'Pedidos de Compra', 'icon' => 'pi pi-list',  'rota' => '#', 'permission' => 'compras.pedidos.visualizar'],
                    ['label' => 'Recebimento',       'icon' => 'pi pi-inbox', 'rota' => '#', 'permission' => 'compras.recebimento.visualizar'],
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

    private function filtrarMenu(array $itens, User $user): array
    {
        $resultado = [];

        foreach ($itens as $item) {
            if (isset($item['filhos'])) {
                $filhosFiltrados = $this->filtrarMenu($item['filhos'], $user);
                if (count($filhosFiltrados) > 0) {
                    $item['filhos'] = $filhosFiltrados;
                    unset($item['permission']);
                    $resultado[] = $item;
                }
                continue;
            }

            if (! isset($item['permission']) || $user->can($item['permission'])) {
                $resultado[] = $item;
            }
        }

        return $resultado;
    }
}
