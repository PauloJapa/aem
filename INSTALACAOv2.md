# Guia de Instalação — ERP Laravel 12

Execute os passos **na ordem**. Cada bloco indica onde rodar: **host** ou **container**.

---

## Pré-requisitos

```bash
# Verificar versões mínimas no host
docker --version          # 24+
docker compose version    # 2.20+
git --version             # qualquer versão recente
```

---

## PASSO 1 — Git no host (raiz do projeto)

> **Onde:** host, na pasta raiz (onde está o `docker-compose.yml`)

```bash
git init
git add docker/ docker-compose.yml .env.example CLAUDE.md README.md
git commit -m "chore: boilerplate docker e configuracao inicial"
```

---

## PASSO 2 — Subir os containers

> **Onde:** host, na pasta raiz

```bash
# Criar a pasta que será montada como volume
mkdir src

# Subir tudo em background
docker compose up -d

# Verificar se todos os containers estão healthy
docker compose ps
```

Aguarde todos os serviços estarem `healthy` antes de continuar.
O postgres costuma demorar 10-20s na primeira vez.

---

## PASSO 3 — Instalar o Laravel

> **Onde:** container `app`

```bash
docker compose exec app bash
```

```bash
# Dentro do container — /var/www é o src/ do host
composer create-project laravel/laravel . "^12.0"

exit
```

---

## PASSO 4 — Commit do Laravel base

> **Onde:** host, dentro da pasta `src/`

```bash
cd src
git add .
git commit -m "chore: instalacao Laravel 12"
cd ..
```

---

## PASSO 5 — Configurar o .env

> **Onde:** host, dentro da pasta `src/`

```bash
# O .env.example da raiz do projeto já tem as configs do Docker
# Copiar para dentro do src/
cp .env.example src/.env
```

Confirmar que o `src/.env` tem essas linhas (ajustar se necessário):

```env
APP_NAME="ERP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=erp
DB_USERNAME=erp_user
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=redis_secret

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=480

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@erp.local"
```

---

## PASSO 6 — Gerar chave e testar conexão

> **Onde:** container `app`

```bash
docker compose exec app bash
```

```bash
php artisan key:generate

# Testar conexão com o banco
php artisan db:show

# Rodar migrations iniciais do Laravel
php artisan migrate

exit
```

Se `db:show` retornar os dados do PostgreSQL, a conexão está ok.

---

## PASSO 7 — Instalar pacotes PHP

> **Onde:** container `app`

```bash
docker compose exec app bash
```

```bash
# Inertia.js — bridge Laravel + Vue
composer require inertiajs/inertia-laravel

# Ziggy — rotas Laravel no frontend JS
composer require tightenco/ziggy

# Spatie Permission — roles e permissões
composer require spatie/laravel-permission

# Spatie Activitylog — auditoria
composer require spatie/laravel-activitylog

# PDF
composer require barryvdh/laravel-dompdf

# Módulos ERP
composer require nwidart/laravel-modules

# Horizon — dashboard e worker de filas
composer require laravel/horizon

# Telescope — debug em dev
composer require laravel/telescope --dev

exit
```

---

## PASSO 8 — Publicar e configurar os pacotes PHP

> **Onde:** container `app`

```bash
docker compose exec app bash
```

```bash
# Inertia — publicar middleware
php artisan inertia:middleware

# Spatie Permission — publicar config e migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Spatie Activitylog — publicar config e migration
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"

# Horizon — publicar config e assets
php artisan horizon:install

# Telescope — publicar config e migrations
php artisan telescope:install

# Laravel Modules — publicar config
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"

# Rodar todas as migrations (permission, activitylog, telescope)
php artisan migrate

exit
```

---

## PASSO 9 — Registrar o middleware do Inertia

> **Onde:** host, editar `src/bootstrap/app.php`

Localizar o bloco `->withMiddleware` e adicionar:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);
})
```

---

## PASSO 10 — Instalar pacotes JavaScript

> **Onde:** container `vite`

```bash
docker compose exec vite sh
```

```bash
# Remover scaffolding padrão do Laravel (vite.config.js e package.json serão reescritos)
rm -f vite.config.js

# Vue 3 + Inertia
npm install @inertiajs/vue3 vue @vitejs/plugin-vue

# PrimeVue 4 + ícones
npm install primevue@^4 primeicons

# Tailwind CSS
npm install -D tailwindcss @tailwindcss/vite

# Estado global
npm install pinia

# Validação de formulários
npm install vee-validate @vee-validate/zod zod

# Ziggy (rotas Laravel no JS)
npm install ziggy-js

exit
```

---

## PASSO 11 — Configurar Vite

> **Onde:** host, criar/substituir `src/vite.config.js`

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',      // necessário para rodar dentro do container
        port: 5173,
        hmr: {
            host: 'localhost', // endereço que o browser acessa
        },
    },
    resolve: {
        alias: {
            '@': '/var/www/resources/js',
        },
    },
})
```

---

## PASSO 12 — Configurar o app.js (entry point Inertia + Vue)

> **Onde:** host, substituir `src/resources/js/app.js`

```js
import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import 'primeicons/primeicons.css'

const pinia = createPinia()

createInertiaApp({
    title: (title) => `${title} — ERP`,

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .use(PrimeVue, { ripple: true })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el)
    },

    progress: {
        color: '#4F46E5',
    },
})
```

---

## PASSO 13 — Shell HTML do Inertia

> **Onde:** host, substituir `src/resources/views/app.blade.php`

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title inertia>{{ config('app.name') }}</title>

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
```

---

## PASSO 14 — Configurar módulos (nwidart)

> **Onde:** container `app` — publicar o provider primeiro (gera o config/modules.php)

```bash
docker compose exec app bash
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
exit
```

> **Onde:** host, editar `src/config/modules.php` (agora o arquivo existe)

Localizar a chave `paths` e confirmar:

```php
'paths' => [
    'modules' => base_path('Modules'),
    ...
]
```

> **Onde:** host, editar `src/composer.json`

Adicionar o autoload dos módulos:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/"
    }
}
```

> **Onde:** container `app`

```bash
docker compose exec app composer dump-autoload
```

---

## PASSO 15 — Configurar Horizon

> **Onde:** host, editar `src/config/horizon.php`

Localizar a chave `environments` e ajustar as filas:

```php
'environments' => [
    'local' => [
        'supervisor-1' => [
            'maxProcesses' => 3,
            'queues'       => ['documents', 'notifications', 'default', 'reports'],
        ],
    ],
    'production' => [
        'supervisor-1' => [
            'maxProcesses'    => 10,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
            'queues'          => ['documents', 'notifications', 'default', 'reports'],
        ],
    ],
],
```

---

## PASSO 16 — Commit do setup completo

> **Onde:** host, dentro da pasta `src/`

```bash
cd src
git add .
git commit -m "chore: instalacao e configuracao da stack completa"
cd ..
```

---

## PASSO 17 — Verificação final

> **Onde:** container `vite` (em paralelo com o app)

```bash
# Terminal 1 — iniciar o Vite dev server
docker compose exec vite npm run dev
```

> **Onde:** browser

| URL | Esperado |
|---|---|
| http://localhost:8080 | Tela do Laravel (ou primeira página Vue) |
| http://localhost:8080/horizon | Dashboard do Horizon |
| http://localhost:8080/telescope | Dashboard do Telescope |
| http://localhost:8025 | Interface do Mailpit |
| http://localhost:5050 | pgAdmin (login: admin@erp.local / admin) |

---

## Resumo dos containers e o que cada um faz

```
docker compose ps

erp_app        → PHP-FPM 8.3 — processa as requisições Laravel
erp_nginx      → Proxy reverso — recebe requests e passa para o PHP
erp_postgres   → Banco de dados PostgreSQL 16
erp_redis      → Cache e broker de filas
erp_horizon    → Worker que processa os Jobs das filas
erp_scheduler  → Executa php artisan schedule:run a cada 60s
erp_vite       → Build e HMR do frontend Vue em dev
erp_pgadmin    → Interface web para administrar o PostgreSQL
erp_mailpit    → Captura todos os emails enviados pela aplicação
```

---

## Próximo passo

Com o ambiente funcionando, o próximo passo é criar o **Módulo Core**:
- Migrations de usuários com perfis e permissões
- Seeder de roles base (admin, gerente, operador)
- Tela de gestão de usuários e controle de acesso
- Comando `module:spec` para gerar documentação de módulo

```bash
docker compose exec app php artisan module:make Core
```
