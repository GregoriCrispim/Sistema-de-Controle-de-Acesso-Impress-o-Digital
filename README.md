# AlunoBem — Controle de acesso e voucher de almoço escolar

Sistema web para **controle de acesso** e **monitoramento de voucher de almoço** em escolas públicas, com **validação por biometria (impressão digital)**, **modo offline** e **relatórios** para auditoria e conferência.

## Visão geral

O AlunoBem cobre o fluxo ponta-a-ponta do almoço escolar:

- **Operação na cantina (Operador)**: terminal de liberação por biometria e liberação manual com motivo.
- **Administração (Administrador)**: gestão de usuários, alunos, digitais, configurações e importações (CSV/Excel).
- **Fiscalização (Fiscal)**: validação de períodos para pagamento com protocolo único.
- **Gestão (Gestão/Coordenação)**: indicadores e relatórios de acompanhamento (somente leitura).
- **Empresa/Fornecedor (Empresa)**: dashboard e relatórios consolidados.

## Stack

- **Backend**: Laravel (rodando em PHP-FPM)
- **Frontend**: Blade + Tailwind CSS + Alpine.js (assets via Vite)
- **Banco de dados**: MySQL 8
- **Infra**: Docker Compose (Nginx + PHP-FPM + MySQL)
- **Testes**: PHPUnit (SQLite em memória)

## Requisitos

- Docker + Docker Compose
- Navegador moderno (Chrome/Firefox/Edge)

## Instalação e configuração (primeira vez)

Na raiz do projeto:

```bash
# 1) Subir os containers (com build da imagem PHP)
docker compose up -d --build

# 2) Instalar dependências PHP (Laravel)
docker compose exec php_alunobem composer install

# 3) Criar o .env da aplicação
docker compose exec php_alunobem cp .env.example .env

# 4) Gerar a chave da aplicação
docker compose exec php_alunobem php artisan key:generate
```

Edite o arquivo `volume_app/.env` e ajuste pelo menos:

- **APP_URL**: `http://localhost:8080`
- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `mysql_alunobem`
- **DB_PORT**: `3306`
- **DB_DATABASE**: `alunobem`
- **DB_USERNAME**: `alunobem`
- **DB_PASSWORD**: `alunobem_secret`

Se for usar login Google (perfil fiscal), configure também:

- **GOOGLE_CLIENT_ID**
- **GOOGLE_CLIENT_SECRET**
- **GOOGLE_REDIRECT_URI**: `http://localhost:8080/auth/google/callback`

Depois de editar o `.env`, limpe o cache de config:

```bash
docker compose exec php_alunobem php artisan config:clear
```

Finalize a instalação criando as tabelas, populando dados e gerando os assets:

```bash
# 5) Criar tabelas e popular dados de teste
docker compose exec php_alunobem php artisan migrate:fresh --seed

# 6) Link de storage (uploads/fotos)
docker compose exec php_alunobem php artisan storage:link

# 7) Instalar dependências JS e gerar build (CSS/JS)
docker compose exec php_alunobem npm install
docker compose exec php_alunobem npm run build
```

## Como rodar (ambiente local via Docker)

Na raiz do projeto:

```bash
# Subir a stack
docker compose up -d
```

Acesse em **`http://localhost:8080`** (o Nginx também publica em `http://localhost` se a porta 80 estiver livre).

### Usuários padrão (após `--seed`)

| Papel | E-mail | Senha | Rota após login |
|-------|--------|-------|-----------------|
| Administrador | admin@alunobem.com | admin123 | `/admin/dashboard` |
| Operador | operador@alunobem.com | operador123 | `/operator/terminal` |
| Empresa | empresa@alunobem.com | empresa123 | `/company/dashboard` |
| Fiscal | fiscal@alunobem.com | fiscal123 | `/fiscal/dashboard` |
| Gestão | gestao@alunobem.com | gestao123 | `/management/dashboard` |

## Popular dados para testes (seed)

O comando abaixo recria o banco e popula dados de demonstração:

```bash
docker compose exec php_alunobem php artisan migrate:fresh --seed
```

O seeder cria:

- usuários padrão (tabela acima)
- configurações iniciais do sistema
- alunos de teste, digitais e histórico de refeições/ocorrências
- fotos de avatar (baixa via internet durante o seed; se falhar, o seed segue e a foto pode ficar vazia)

## Testes automatizados

```bash
docker compose exec php_alunobem php artisan test
```

Observação: os testes usam **SQLite em memória**, então não alteram seu MySQL do Docker.

## Limpar / resetar depois de testar

Escolha a opção adequada:

### Resetar dados da aplicação (mantém o MySQL/volume)

```bash
docker compose exec php_alunobem php artisan migrate:fresh --seed
```

### Parar os containers (mantém dados)

```bash
docker compose down
```

### Remover tudo (inclui volume do MySQL)

Isso apaga completamente o banco do Docker (volume `mysql_data`).

```bash
docker compose down -v
```

## Comandos úteis

```bash
# Logs (Nginx / PHP / MySQL)
docker compose logs -f

# Entrar no container da aplicação
docker compose exec php_alunobem bash

# Rodar migrations sem seed (apenas estrutura)
docker compose exec php_alunobem php artisan migrate
```

## Estrutura do projeto

```
.
├── docker-compose.yml
├── docker/
│   ├── nginx/
│   └── php/
└── volume_app/                # Laravel (app, routes, database, tests, etc.)
```

## Produção (resumo)

- Ajuste o `APP_URL` para o domínio real.
- Configure credenciais e integrações no `volume_app/.env` (ex.: Google OAuth do fiscal).
- Execute otimizações: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.

