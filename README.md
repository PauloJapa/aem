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
