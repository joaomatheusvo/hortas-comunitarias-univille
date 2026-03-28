# Guia do Backend

[Documentacao do Banco de Dados](../docs/db/README.md)

[Documentacao da API REST](../docs/api/README.md)

## Setup Local Hibrido

Parte do setup com containers e parte local costuma performar melhor no Windows.

### Requisitos

- Docker Desktop
- Composer
- PHP 8.1+

## Jeito Recomendado de Rodar

Na raiz do projeto:

```bash
copy backend\.env.example backend\.env
docker compose up -d mysql php nginx phpmyadmin redis
docker compose exec php composer config --global audit.block-insecure false
docker compose exec php composer config --global process-timeout 0
docker compose exec php composer install --prefer-dist --no-progress
```

Importante:

- O backend responde em `http://localhost:8181/api/v1`
- O login funciona em `POST http://localhost:8181/api/v1/sessoes/login`
- Se existir conflito de configuracao no Nginx, mantenha desativado `docker/nginx/sites/ci.conf.disabled` no ambiente local

### Variaveis de Ambiente

Crie `backend/.env` com base em `backend/.env.example`.

Exemplo:

```env
# Environment
APP_ENV=development
APP_DEBUG=true

# Database
DB_HOST=mysql
DB_NAME=railway
DB_USER=hortas_user
DB_PASS=hortas_password
DB_CHARSET=utf8mb4

# JWT
JWT_SECRET=hortas_dev
JWT_ALGORITHM=HS256
JWT_TTL=7200

# API
API_VERSION=v1
```

### URLs de Acesso

- Backend API: `http://localhost:8181/api/v1`
- phpMyAdmin: `http://localhost:8080`

## Banco de Dados e Seeds

Se o banco ainda nao estiver populado, rode os SQLs da pasta `backend/src/Utils/SQL` na ordem:

1. `00_SQL_criar_banco.sql`
2. `01_SQL_seed_dados_iniciais.sql`
3. `02_SQL_seed_dados_teste.sql`

Voce pode executar pelo phpMyAdmin ou importar via terminal.

Para listar os usuarios carregados:

```bash
docker compose exec mysql mysql -u hortas_user -phortas_password railway -e "SELECT email, nome_completo, cpf FROM usuarios;"
```

## Login de Teste

Exemplo de teste:

```bash
curl -X POST http://localhost:8181/api/v1/sessoes/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin_assoc_1@example.com\",\"senha\":\"senha12345\"}"
```

## Melhorias de Instancias Aplicadas

O backend continua usando PHP-DI como container principal, mas agora a criacao de instancias na autenticacao e autorizacao ficou mais centralizada.

### O que foi implementado e alterado

As mudancas desta etapa foram focadas em organizacao da camada de autenticacao/autorizacao, reducao de acoplamento e padronizacao das respostas HTTP do backend.

- `JwtConfig` foi criado para encapsular segredo, algoritmo e TTL do token
- `JwtService` centraliza `encode` e `decode` de JWT
- `JsonResponseFactory` padroniza respostas JSON nos middlewares
- `backend/config/auth.php` passou a registrar essas dependencias no container do PHP-DI
- `JwtMiddleware` deixou de depender de `$_ENV`, `JWT::decode(...)` e `new Response()` direto
- `RoutePermissionMiddleware` agora usa injecao de dependencia e a mesma fabrica de resposta padronizada
- `SessaoService` deixou de gerar token diretamente com `JWT::encode(...)` e passou a respeitar o TTL configurado
- A geracao e validacao de token ficaram centralizadas em uma unica camada de suporte
- As respostas de erro de autenticacao e permissao ficaram consistentes em JSON

### Resultado pratico da refatoracao

- Menos instanciacao manual espalhada pelo codigo
- Menor acoplamento com variaveis globais
- Regras de JWT concentradas em um ponto unico
- Reaproveitamento maior entre login, cadastro e middlewares
- Base mais limpa para testes, manutencao e futuras refatoracoes
- Melhor legibilidade da arquitetura para documentacao e apresentacao academica

### Arquivos Novos

- [`backend/src/Support/JwtConfig.php`](./src/Support/JwtConfig.php)
- [`backend/src/Support/JwtService.php`](./src/Support/JwtService.php)
- [`backend/src/Support/JsonResponseFactory.php`](./src/Support/JsonResponseFactory.php)

### Arquivos Atualizados

- [`backend/config/auth.php`](./config/auth.php)
- [`backend/src/Middlewares/JwtMiddleware.php`](./src/Middlewares/JwtMiddleware.php)
- [`backend/src/Middlewares/RoutePermissionMiddleware.php`](./src/Middlewares/RoutePermissionMiddleware.php)
- [`backend/src/Services/SessaoService.php`](./src/Services/SessaoService.php)

### Ajuste de ambiente local relacionado ao backend

- O arquivo de configuracao `docker/nginx/sites/ci.conf` foi desativado no ambiente local e mantido como `docker/nginx/sites/ci.conf.disabled` para evitar conflito com a execucao local da API

### Script de teste manual

- O arquivo [`backend/testar-backend.ps1`](./testar-backend.ps1) pode ser usado para validar rapidamente os endpoints de login e cadastro em ambiente local

## Comandos Uteis do Docker

```bash
# Parar todos os containers
docker ps -aq | ForEach-Object { docker stop $_ }

# Remover todos os containers
docker ps -aq | ForEach-Object { docker rm $_ }

# Remover todas as imagens
docker images -q | ForEach-Object { docker rmi -f $_ }
```
