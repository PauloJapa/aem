-- Extensões úteis para o ERP
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";     -- UUIDs nativos
CREATE EXTENSION IF NOT EXISTS "unaccent";      -- busca sem acento (autocomplete)
CREATE EXTENSION IF NOT EXISTS "pg_trgm";       -- busca por similaridade de texto

-- Índice trigram para autocomplete rápido em nome/documento
-- (as migrations do Laravel vão criar os índices específicos,
--  mas essas extensões precisam existir antes)
