<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleSpecCommand extends Command
{
    protected $signature = 'module:spec
                            {module : Nome do módulo (ex: Financeiro)}
                            {--force : Sobrescrever spec.md se já existir}';

    protected $description = 'Gera o arquivo docs/spec.md de documentação para um módulo';

    public function handle(): int
    {
        $module     = Str::studly($this->argument('module'));
        $modulePath = base_path("Modules/{$module}");
        $docsPath   = "{$modulePath}/docs";
        $specPath   = "{$docsPath}/spec.md";

        // ── Validações ────────────────────────────────────────────────────────
        if (! is_dir($modulePath)) {
            $this->error("Módulo [{$module}] não encontrado em Modules/{$module}.");
            $this->line('  Crie o módulo primeiro com: <comment>php artisan module:make ' . $module . '</comment>');
            return self::FAILURE;
        }

        if (file_exists($specPath) && ! $this->option('force')) {
            $this->warn("Arquivo docs/spec.md já existe no módulo [{$module}].");
            $this->line('  Use <comment>--force</comment> para sobrescrever.');
            return self::FAILURE;
        }

        // ── Criar pasta docs/ se não existir ─────────────────────────────────
        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }

        // ── Gerar o spec.md ───────────────────────────────────────────────────
        file_put_contents($specPath, $this->buildSpec($module));

        $this->info("Spec gerado: <comment>Modules/{$module}/docs/spec.md</comment>");
        $this->newLine();
        $this->line('  Próximos passos:');
        $this->line('  1. Preencher <comment>## Objetivo</comment>');
        $this->line('  2. Listar <comment>## Entidades</comment>');
        $this->line('  3. Documentar <comment>## Regras de Negócio</comment>');
        $this->line('  4. Definir <comment>## Permissões</comment>');

        return self::SUCCESS;
    }

    private function buildSpec(string $module): string
    {
        $date  = now()->format('d/m/Y');
        $kebab = Str::kebab($module);

        $lines = [
            "# Módulo {$module}",
            "",
            "> **Criado em:** {$date}  ",
            "> **Status:** 🔴 Não iniciado  ",
            ">",
            "> Status possíveis: 🔴 Não iniciado · 🟡 Em desenvolvimento · 🟢 Produção",
            "",
            "---",
            "",
            "## Objetivo",
            "",
            "<!-- O que esse módulo resolve? Descreva em 2-3 linhas o propósito principal. -->",
            "",
            "---",
            "",
            "## Entidades Principais",
            "",
            "<!-- Liste os models/tabelas principais e o que cada um representa. -->",
            "",
            "| Entidade | Tabela | Descrição |",
            "|---|---|---|",
            "| Exemplo | {$kebab}_exemplos | Descrição da entidade |",
            "",
            "---",
            "",
            "## Regras de Negócio",
            "",
            "<!-- Regras que o módulo deve respeitar.",
            "     Cada item aqui vira um teste unitário no Service. -->",
            "",
            "- [ ] RN001 —",
            "- [ ] RN002 —",
            "- [ ] RN003 —",
            "",
            "---",
            "",
            "## Permissões do Módulo",
            "",
            "<!-- Nomenclatura: modulo.recurso.acao -->",
            "",
            "| Permission | O que permite |",
            "|---|---|",
            "| {$kebab}.visualizar | Acessar o módulo |",
            "| {$kebab}.criar | Criar novos registros |",
            "| {$kebab}.editar | Editar registros existentes |",
            "| {$kebab}.excluir | Excluir registros |",
            "",
            "---",
            "",
            "## Fluxos Principais",
            "",
            "<!-- Um fluxo por seção, em passos numerados. -->",
            "",
            "### Fluxo 1 —",
            "",
            "1. ...",
            "2. ...",
            "3. ...",
            "",
            "---",
            "",
            "## Integrações com Outros Módulos",
            "",
            "| Módulo | Direção | Descrição |",
            "|---|---|---|",
            "| Core | ← consome | Autenticação e permissões |",
            "",
            "---",
            "",
            "## Jobs / Filas",
            "",
            "| Job | Fila | Quando dispara |",
            "|---|---|---|",
            "| — | — | — |",
            "",
            "---",
            "",
            "## Relatórios e PDFs",
            "",
            "| Relatório | Formato | Descrição |",
            "|---|---|---|",
            "| — | — | — |",
            "",
            "---",
            "",
            "## Parâmetros de Sistema",
            "",
            "<!-- Configuráveis via tela de parâmetros (tabela `parametros`).",
            "     Nomenclatura: modulo.recurso.chave -->",
            "",
            "| Chave | Valor padrão | Descrição |",
            "|---|---|---|",
            "| — | — | — |",
            "",
            "---",
            "",
            "## Decisões Técnicas",
            "",
            "<!-- Registre decisões de implementação e o motivo.",
            "     Evita que a mesma discussão aconteça duas vezes. -->",
            "",
            "- **[{$date}]** —",
            "",
            "---",
            "",
            "## Pendências / TODO",
            "",
            "- [ ] ...",
            "",
        ];

        return implode("\n", $lines);
    }
}
