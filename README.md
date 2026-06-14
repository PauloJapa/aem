# ERP Boilerplate — Laravel 12

## Pré-requisitos
- Docker + Docker Compose v2
- Git

## Setup inicial

```bash
# 1. Clonar e entrar na pasta
git clone <repo> erp && cd erp

# 2. Copiar e ajustar variáveis de ambiente
cp .env.example .env

# 3. Subir os containers
docker compose up -d

# 4. Instalar dependências PHP
docker compose exec app composer install

# 5. Gerar chave da aplicação
docker compose exec app php artisan key:generate

# 6. Rodar migrations e seeders
docker compose exec app php artisan migrate --seed

# 7. Instalar dependências JS e buildar
docker compose exec vite npm install
```

## URLs de acesso (dev)

| Serviço | URL |
|---|---|
| Aplicação | http://localhost:8080 |
| Horizon (filas) | http://localhost:8080/horizon |
| Telescope (debug) | http://localhost:8080/telescope |
| Mailpit (emails) | http://localhost:8025 |
| pgAdmin (banco) | http://localhost:5050 |

## Comandos frequentes

```bash
# Entrar no container
docker compose exec app bash

# Testes
docker compose exec app php artisan test

# Novo módulo ERP
docker compose exec app php artisan module:make NomeModulo

# Migrations
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback

# Limpar cache
docker compose exec app php artisan optimize:clear

# Ver logs das filas
docker compose logs -f horizon
```
# <============== Comandos para instalação ===================>
1. No host — inicia o Git antes de qualquer coisa
bash# Na raiz do projeto (onde está o docker-compose.yml)
git init
git add docker/ docker-compose.yml .env.example CLAUDE.md README.md
git commit -m "chore: boilerplate docker e configuração inicial"
2. No host — cria a pasta src e instala o Laravel via container
bashmkdir src
3. No container — instala o Laravel dentro de src/
bash# Conecta no container
docker compose exec app bash

# Dentro do container, o /var/www é o seu src/ no host
composer create-project laravel/laravel . "^12.0"

# Sai do container
exit
4. No host — commita o Laravel recém instalado
bashcd src
# O .gitignore do Laravel já veio na instalação
git add .
git commit -m "chore: instalação Laravel 12"

Por que instalar via container e não direto no host?
Porque o Composer roda com o PHP do container (8.3 com as extensões certas), não com o PHP do seu host que pode ser outra versão ou não ter pgsql, redis, etc. Garante que o ambiente é consistente desde o primeiro comando.

Depois da instalação, os próximos passos serão
bash# Dentro do container — configurar .env e rodar migrations
docker compose exec app bash

cp .env.example .env          # já tem as configs do docker
php artisan key:generate
php artisan migrate

# Instalar os pacotes da stack
composer require \
  inertiajs/inertia-laravel \
  tightenco/ziggy \
  spatie/laravel-permission \
  spatie/laravel-activitylog \
  barryvdh/laravel-dompdf \
  nwidart/laravel-modules \
  laravel/horizon \
  laravel/telescope

# Frontend
exit
docker compose exec vite npm install @inertiajs/vue3 vue @vitejs/plugin-vue \
  primevue primeicons pinia vee-validate @vee-validate/zod zod \
  tailwindcss @tailwindcss/vite