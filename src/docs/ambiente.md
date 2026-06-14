# Ambiente de Desenvolvimento

## Pré-requisitos

- Docker + Docker Compose v2
- Git

Não é necessário PHP, Node ou Composer instalados localmente — tudo roda dentro dos containers.

---

## Setup inicial (primeira vez)

```bash
# 1. Clonar o repositório
git clone <repo> && cd aem

# 2. Criar os dois arquivos .env
cp .env.example .env                  # Docker Compose (raiz)
cp src/.env.example src/.env          # Laravel (src/)

# 3. Editar src/.env com as senhas desejadas
# Garantir que REDIS_PASSWORD em src/.env == REDIS_PASSWORD em .env (raiz)

# 4. Subir os containers
docker compose up -d

# 5. Gerar a chave da aplicação
docker compose exec app php artisan key:generate

# 6. Rodar as migrations
docker compose exec app php artisan migrate

# 7. (Opcional) Rodar seeders
docker compose exec app php artisan db:seed
```

---

## Serviços e URLs

| Serviço | URL | Credenciais |
|---|---|---|
| Aplicação | http://localhost:8080 | — |
| pgAdmin | http://localhost:5050 | admin@erp.dev / admin |
| Mailpit (emails) | http://localhost:8025 | — |
| Horizon (filas) | http://localhost:8080/horizon | — |
| Telescope (debug) | http://localhost:8080/telescope | — |
| Vite (HMR) | http://localhost:5173 | — |

---

## Comandos do dia a dia

```bash
# Entrar no container principal
docker compose exec app bash

# Artisan
docker compose exec app php artisan <comando>

# Migrations
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed

# Testes
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=NomeDoTeste

# Limpar todos os caches
docker compose exec app php artisan optimize:clear

# Logs em tempo real
docker compose logs -f app

# Horizon (filas) — monitorar workers
docker compose logs -f horizon
```

---

## Importante: dois arquivos .env

```
/var/www/aem/
├── .env              ← Docker Compose: resolve ${VARS} no docker-compose.yml
└── src/
    └── .env          ← Laravel: lido pela aplicação em runtime
```

**As senhas devem estar sincronizadas entre os dois arquivos.**
Se mudar `REDIS_PASSWORD` ou `DB_PASSWORD`, atualizar nos dois e recriar o container:

```bash
docker compose up -d redis     # recriar Redis com nova senha
docker compose exec app php artisan optimize:clear
```

---

## Criar um novo módulo

```bash
docker compose exec app php artisan module:make NomeModulo
docker compose exec app php artisan module:spec NomeModulo
# → preencher src/Modules/NomeModulo/docs/spec.md antes de codar
```

Ver [modulos.md](modulos.md) para o fluxo completo.
