# Tarefa: Criar AppLayout — Layout Base do ERP

## Contexto
Stack: Laravel 12 + Inertia.js + Vue 3 + PrimeVue 4 + Tailwind CSS + Pinia  
Tema Teal já configurado em `resources/js/theme/erp-preset.js`  
Store de tema em `resources/js/stores/useThemeStore.js`  
CSS base com variáveis `--erp-*` em `resources/css/app.css`

---

## Arquivos a criar

```
resources/js/
├── Layouts/
│   └── AppLayout.vue          ← layout principal (criar)
├── Components/
│   ├── Layout/
│   │   ├── AppSidebar.vue     ← sidebar colapsável (criar)
│   │   ├── AppHeader.vue      ← header fixo (criar)
│   │   └── AppNotificacoes.vue ← dropdown de notificações (criar)
│   └── UI/
│       └── AppAvatar.vue      ← avatar com iniciais fallback (criar)
└── stores/
    └── useNotificacaoStore.js ← store de notificações (criar)
```

---

## 1. `useNotificacaoStore.js`

Store Pinia com:

```js
// Estado
notificacoes: ref([])   // array de objetos
naoLidas: computed()    // count de !lida

// Estrutura de cada notificação:
{
  id: Number,
  tipo: 'info' | 'sucesso' | 'alerta' | 'erro' | 'confirm',
  titulo: String,
  mensagem: String,
  lida: Boolean,
  criadoEm: Date,
}

// Actions
marcarComoLida(id)
marcarTodasComoLidas()
adicionar(notificacao)   // para uso futuro via websocket/polling
remover(id)
```

Seed com 3 notificações de exemplo (tipos variados) para visualização inicial.

---

## 2. `AppAvatar.vue`

Props:
- `nome` (String) — nome do usuário
- `foto` (String, opcional) — URL da foto
- `tamanho` (String, default `'md'`) — `'sm' | 'md' | 'lg'`

Comportamento:
- Se `foto` fornecida: exibe `<img>`
- Se não: exibe círculo com as iniciais (primeiras letras do primeiro e último nome)
- Fundo do círculo: `teal-600`, texto branco
- Tamanhos: sm=28px, md=36px, lg=44px

---

## 3. `AppNotificacoes.vue`

Usar `Popover` do PrimeVue (abre ao clicar no sino).

Estrutura visual:
```
[ 🔔 (badge com count) ]
  ↓ ao clicar abre Popover:
┌─────────────────────────────┐
│ Notificações        [Limpar]│
├─────────────────────────────┤
│ 🔵 Título da notificação    │  ← info
│    Mensagem resumida   2min │
├─────────────────────────────┤
│ 🟢 Título                   │  ← sucesso
│    Mensagem            5min │
├─────────────────────────────┤
│ 🟡 Título                   │  ← alerta
│    Mensagem           10min │
├─────────────────────────────┤
│      Ver todas              │
└─────────────────────────────┘
```

- Ícone do sino: `pi pi-bell` do PrimeIcons
- Badge vermelho com `naoLidas` count — some se zero
- Cada item: clique marca como lida e fecha o popover
- Cores dos ícones por tipo: usar variáveis `--erp-badge-*` do CSS
- "Ver todas" → link para rota futura `notificacoes.index` (usar `#` por enquanto)
- "Limpar" → chama `marcarTodasComoLidas()`
- Tempo relativo: usar função simples (agora, Xmin, Xh, ontem)

---

## 4. `AppSidebar.vue`

Props:
- `collapsed` (Boolean) — recebe do AppLayout via useThemeStore

Estrutura:
```
┌──────────────────┐
│ [logo] ERP       │  ← .erp-sidebar__logo (esconde texto se collapsed)
├──────────────────┤
│ nav items        │  ← .erp-sidebar__nav
│  ├ item simples  │
│  └ item c/ sub   │
│    ├ subitem 1   │
│    └ subitem 2   │
├──────────────────┤
│ [avatar] usuário │  ← .erp-sidebar__footer
└──────────────────┘
```

**Itens do menu** — definir como array de objetos no próprio componente por enquanto:

```js
const menu = [
  { label: 'Dashboard',  icon: 'pi pi-home',          rota: 'dashboard' },
  { label: 'Cadastro',   icon: 'pi pi-database',       filhos: [
    { label: 'Clientes',     icon: 'pi pi-users',       rota: '#' },
    { label: 'Fornecedores', icon: 'pi pi-truck',       rota: '#' },
    { label: 'Produtos',     icon: 'pi pi-box',         rota: '#' },
  ]},
  { label: 'Vendas',     icon: 'pi pi-shopping-cart',  filhos: [
    { label: 'Pedidos',      icon: 'pi pi-list',        rota: '#' },
    { label: 'Orçamentos',   icon: 'pi pi-file',        rota: '#' },
  ]},
  { label: 'Compras',    icon: 'pi pi-shopping-bag',   filhos: [
    { label: 'Pedidos de Compra', icon: 'pi pi-list',  rota: '#' },
    { label: 'Recebimento',       icon: 'pi pi-inbox', rota: '#' },
  ]},
  { label: 'Financeiro', icon: 'pi pi-wallet',         filhos: [
    { label: 'Contas a Pagar',   icon: 'pi pi-arrow-up-right',   rota: '#' },
    { label: 'Contas a Receber', icon: 'pi pi-arrow-down-left',  rota: '#' },
    { label: 'Fluxo de Caixa',   icon: 'pi pi-chart-line',       rota: '#' },
  ]},
  { label: 'Estoque',    icon: 'pi pi-warehouse',      filhos: [
    { label: 'Movimentações', icon: 'pi pi-arrows-v',   rota: '#' },
    { label: 'Inventário',    icon: 'pi pi-clipboard',  rota: '#' },
  ]},
  { label: 'DRE',        icon: 'pi pi-chart-bar',      rota: '#' },
]
```

**Comportamento submenu:**
- Modo expandido: clicar no item pai abre/fecha os filhos com animação (slide down)
- Só um grupo aberto por vez (fechar o anterior ao abrir novo)
- Item ativo: detectar pela rota atual (`usePage().url`)
- Modo colapsado: ao passar o mouse sobre item com filhos → mostrar `Tooltip` do PrimeVue com o nome do item (não os subitens — tooltip simples)

**Footer da sidebar:**
- Exibe `AppAvatar` + nome do usuário (esconde nome se collapsed)
- Dados do usuário: `usePage().props.auth.user`

---

## 5. `AppHeader.vue`

```
┌─────────────────────────────────────────────────────────────┐
│ [≡ toggle]  Breadcrumb atual          [🌙] [🔔] [Avatar ▾] │
└─────────────────────────────────────────────────────────────┘
```

Elementos da esquerda:
- **Botão toggle sidebar**: ícone `pi pi-bars` — chama `themeStore.toggleSidebar()`

- **Breadcrumb**: usar `Breadcrumb` do PrimeVue
  - Recebe prop `items` do AppLayout
  - Default: `[{ label: 'Início', icon: 'pi pi-home' }]`

Elementos da direita:
- **Toggle dark/light**: ícone `pi pi-moon` (light) / `pi pi-sun` (dark) — chama `themeStore.toggleDark()`
- **Notificações**: componente `AppNotificacoes`
- **Menu do usuário**: `AppAvatar` + nome — ao clicar abre Menu do PrimeVue com:
  - `pi pi-user` Meu Perfil → `#`
  - `pi pi-cog` Configurações → `#`
  - separador
  - `pi pi-sign-out` Sair → POST para `route('logout')`

---

## 6. `AppLayout.vue`

Layout principal que une tudo:

```vue
<template>
  <div class="erp-layout" :class="{ dark: themeStore.isDark }">

    <AppSidebar :collapsed="themeStore.sidebarCollapsed" />

    <div class="erp-main" :class="{ 'erp-main--collapsed': themeStore.sidebarCollapsed }">

      <AppHeader :breadcrumb="breadcrumb" />

      <main class="erp-content">
        <slot />
      </main>

    </div>
  </div>
</template>
```

Props:
- `breadcrumb` (Array, default `[]`) — repassado para AppHeader

`onMounted`: chamar `themeStore.init()`

---

## Como usar em uma Page Vue (Inertia)

```vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

// ou com breadcrumb:
const layout = (h, page) =>
  h(AppLayout, { breadcrumb: [
    { label: 'Financeiro' },
    { label: 'Contas a Pagar' },
  ]}, () => page)
</script>
```

---

## Ordem de execução

1. Criar `useNotificacaoStore.js`
2. Criar `AppAvatar.vue`
3. Criar `AppNotificacoes.vue`
4. Criar `AppSidebar.vue`
5. Criar `AppHeader.vue`
6. Criar `AppLayout.vue`
7. Criar página de teste `resources/js/Pages/Dashboard.vue` que usa o layout para verificar visualmente

---

## Página Dashboard.vue para teste

Criar uma página mínima que use o `AppLayout` e exiba:
- Título "Dashboard"
- 4 cards simples com números fictícios (Vendas do mês, Contas a pagar, Contas a receber, Estoque baixo)
- Usar componente `Card` do PrimeVue

Breadcrumb: `[{ label: 'Dashboard', icon: 'pi pi-home' }]`

---

## Verificação final

- [ ] Sidebar expande e colapsa com animação suave
- [ ] No modo colapsado, hover em item com filhos mostra tooltip com nome
- [ ] Submenu abre/fecha com animação, só um aberto por vez
- [ ] Header fixo — não some ao rolar o conteúdo
- [ ] Toggle dark/light aplica classe `.dark` no `<html>` e muda o visual
- [ ] Preferência de dark mode e sidebar persiste ao recarregar a página
- [ ] Notificações abrem em popover com badge de contagem
- [ ] Menu do usuário abre ao clicar no avatar com as opções corretas
- [ ] Logout faz POST para a rota correta
- [ ] Breadcrumb atualiza conforme a prop recebida

## Build limpo — 854 módulos, sem erros. Resumo do que foi criado:

- Arquivo	O que faz
- useNotificacaoStore.js	Store com 3 notificações seed, naoLidas computed, marcar/remover
- AppAvatar.vue	Avatar com fallback de iniciais, 3 tamanhos
- AppNotificacoes.vue	Sino com badge + Popover listando notificações
- AppSidebar.vue	Sidebar colapsável, submenus com slide, tooltip no modo recolhido
- AppHeader.vue	Header fixo com breadcrumb, dark toggle, notificações e menu do usuário
- AppLayout.vue	Layout principal que une tudo via themeStore
- Dashboard.vue	Página de teste com 4 cards — acessar em /dashboard

- Também ajustado no backend:
- bootstrap/app.php — middleware HandleInertiaRequests registrado
- HandleInertiaRequests.php — auth.user nos shared props
- routes/web.php — rotas dashboard e logout
- app.js — diretiva v-tooltip registrada globalmente