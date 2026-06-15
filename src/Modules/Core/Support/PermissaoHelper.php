<?php

namespace Modules\Core\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissaoHelper
{
    // Agrupa permissions pelo padrão modulo.recurso.acao
    // Retorna: ['Financeiro' => ['Contas A Pagar' => ['visualizar' => 'financeiro.contas-pagar.visualizar']]]
    public static function agruparParaMatriz(Collection $permissions): array
    {
        $resultado = [];

        foreach ($permissions as $perm) {
            $partes = explode('.', $perm->name);

            if (count($partes) === 2) {
                [$modulo, $acao] = $partes;
                $moduloLabel = Str::title(str_replace('-', ' ', $modulo));
                $resultado[$moduloLabel]['(geral)'][$acao] = $perm->name;
                continue;
            }

            if (count($partes) < 3) {
                continue;
            }

            [$modulo, $recurso, $acao] = $partes;
            $moduloLabel  = Str::title(str_replace('-', ' ', $modulo));
            $recursoLabel = Str::title(str_replace('-', ' ', $recurso));
            $resultado[$moduloLabel][$recursoLabel][$acao] = $perm->name;
        }

        ksort($resultado);
        foreach ($resultado as &$recursos) {
            ksort($recursos);
        }

        return $resultado;
    }

    // Coleta todas as ações únicas presentes nas permissions agrupadas
    public static function acoesUnicas(array $agrupadas): array
    {
        $acoes = [];
        foreach ($agrupadas as $recursos) {
            foreach ($recursos as $permsPorAcao) {
                foreach (array_keys($permsPorAcao) as $acao) {
                    $acoes[$acao] = true;
                }
            }
        }

        // Ordenar: ações comuns primeiro
        $ordem = ['visualizar', 'criar', 'editar', 'excluir', 'aprovar', 'gerenciar'];
        $resultado = [];
        foreach ($ordem as $a) {
            if (isset($acoes[$a])) {
                $resultado[] = $a;
                unset($acoes[$a]);
            }
        }
        return array_merge($resultado, array_keys($acoes));
    }
}
