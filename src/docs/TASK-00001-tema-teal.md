# Tarefa: Configurar Tema Teal no PrimeVue 4

## Contexto
Stack: Laravel 12 + Inertia.js + Vue 3 + PrimeVue 4 + Tailwind CSS + Pinia
Os arquivos fonte estão em `src/resources/`

## O que fazer

### 1. Instalar dependência do tema Aura do PrimeVue
```bash
npm install @primevue/themes
```

### 2. Criar `src/resources/js/theme/erp-preset.js`
Conteúdo: preset do PrimeVue 4 baseado no Aura com paleta Teal (Tailwind scale).
- Paleta `primary` usando a escala teal completa (50 a 950)
- Paleta `surface` usando slate para fundos e bordas
- Tokens de `colorScheme.light` e `colorScheme.dark` configurados
- Tokens de componentes: `button`, `inputtext`, `card`, `badge` com `borderRadius: '0.375rem'`
- Dark mode: primary color muda de `teal.600` (light) para `teal.400` (dark)

Valores da escala teal:
```
50:  '#f0fdfa'
100: '#ccfbf1'
200: '#99f6e4'
300: '#5eead4'
400: '#2dd4bf'
500: '#14b8a6'
600: '#0d9488'
700: '#0f766e'
800: '#115e59'
900: '#134e4a'
950: '#042f2e'
```

Valores da escala slate (surface):
```
0:   '#ffffff'
50:  '#f8fafc'
100: '#f1f5f9'
200: '#e2e8f0'
300: '#cbd5e1'
400: '#94a3b8'
500: '#64748b'
600: '#475569'
700: '#334155'
800: '#1e293b'
900: '#0f172a'
950: '#020617'
```

### 3. Substituir `src/resources/js/app.js`
- Importar `ErpPreset` de `./theme/erp-preset`
- Importar `DialogService` de `primevue/dialogservice`
- Configurar `PrimeVue` com:
  - `theme.preset: ErpPreset`
  - `theme.options.darkModeSelector: '.dark'`
  - `theme.options.cssLayer: false`
  - `locale` PT-BR completo (dayNames, monthNames, dateFormat: 'dd/mm/yy', etc.)
- Registrar `.use(DialogService)`
- `progress.color: '#14b8a6'`

### 4. Substituir `src/resources/css/app.css`
Estrutura:
```css
@import "tailwindcss";
```
Seguido de variáveis CSS (`--erp-*`) em `:root` e `.dark`:

**Sidebar:**
- `--erp-sidebar-width: 240px`
- `--erp-sidebar-collapsed-width: 64px`
- `--erp-sidebar-bg: #115e59` (teal-800)
- `--erp-sidebar-text: #ccfbf1` (teal-100)
- `--erp-sidebar-active-bg: #0f766e` (teal-700)
- `--erp-sidebar-hover-bg: rgba(255,255,255,0.08)`
- `--erp-sidebar-icon: #5eead4` (teal-300)

**Header:**
- `--erp-header-height: 56px`
- `--erp-header-bg: #ffffff`
- `--erp-header-border: #e2e8f0`

**Notificações:**
- `--erp-badge-info: #3b82f6`
- `--erp-badge-sucesso: #22c55e`
- `--erp-badge-alerta: #f59e0b`
- `--erp-badge-erro: #ef4444`
- `--erp-badge-confirm: #14b8a6`

**Dark mode (`.dark`):**
- `--erp-sidebar-bg: #042f2e` (teal-950)
- `--erp-sidebar-active-bg: #134e4a` (teal-900)
- `--erp-header-bg: #0f172a` (slate-900)
- `--erp-header-border: #1e293b` (slate-800)

**Classes de layout** (sem Tailwind utilitário — CSS puro):
- `.erp-layout` — `display:flex; min-height:100vh`
- `.erp-main` — `flex:1; margin-left: var(--erp-sidebar-width); transition`
- `.erp-main--collapsed` — `margin-left: var(--erp-sidebar-collapsed-width)`
- `.erp-header` — `height: var(--erp-header-height); position:sticky; top:0; z-index:40`
- `.erp-content` — `flex:1; padding:1.5rem`
- `.erp-sidebar` — `position:fixed; width: var(--erp-sidebar-width); transition; z-index:50`
- `.erp-sidebar--collapsed` — `width: var(--erp-sidebar-collapsed-width)`
- `.erp-sidebar__logo`, `.erp-sidebar__nav`, `.erp-sidebar__item`, `.erp-sidebar__icon`, `.erp-sidebar__label`, `.erp-sidebar__footer`
- `.erp-sidebar__item--active` — `background: var(--erp-sidebar-active-bg); color:#fff`
- `.erp-sidebar--collapsed .erp-sidebar__label` — `opacity:0; width:0`

### 5. Criar `src/resources/js/stores/useThemeStore.js`
Store Pinia com:
- `isDark` (ref) — lê `localStorage('erp:dark')` ou `prefers-color-scheme`
- `sidebarCollapsed` (ref) — lê `localStorage('erp:sidebar-collapsed')`
- `init()` — chama na montagem do AppLayout
- `toggleDark()` — alterna e aplica classe `.dark` no `document.documentElement`
- `toggleSidebar()` — alterna colapso
- Watchers que persistem no localStorage automaticamente

## Ordem de execução
1. `npm install @primevue/themes`
2. Criar `theme/erp-preset.js`
3. Criar `stores/useThemeStore.js`
4. Substituir `app.js`
5. Substituir `app.css`
6. Rodar `npm run dev` e verificar se sem erros no console

## Verificação
Após executar, confirmar:
- `npm run dev` sem erros
- Variáveis `--erp-*` visíveis no DevTools > Elements > :root
- `window.__pinia` disponível no console do browser


## Build limpo — 812 módulos, sem erros. Resumo do que foi feito:

- Arquivo	Status
- theme/erp-preset.js	Criado — preset Aura com paleta Teal/Slate
- stores/useThemeStore.js	Criado — dark mode + sidebar collapse com localStorage
- app.js	Atualizado — PrimeVue com preset, locale PT-BR, DialogService
- app.css	Atualizado — variáveis --erp-* + classes de layout
- Bônus descoberto e corrigido: ziggy-js não estava instalado no npm (estava só no composer.json). Instalado e adicionado ao package.json.