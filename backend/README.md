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

<<<<<<< HEAD
```powershell
copy backend\.env.example backend\.env
docker compose up -d mysql php nginx phpmyadmin redis
```

Se aparecer conflito de nome de container (`hortas_mysql already in use`), os containers ja existem. Inicie-os com:

```powershell
docker start hortas_mysql hortas_php hortas_nginx hortas_phpmyadmin hortas_redis hortas_frontend
```

Instale as dependencias PHP dentro do container:

```powershell
docker exec hortas_php composer config --global audit.block-insecure false
docker exec hortas_php composer config --global process-timeout 0
docker exec hortas_php composer install --prefer-dist --no-progress --working-dir=/var/www/backend
```

> **Nota:** Se `docker compose exec php` retornar `service "php" is not running`, use `docker exec hortas_php` — os containers podem ter sido criados fora do contexto atual do Compose.

=======
```bash
copy backend\.env.example backend\.env
docker compose up -d mysql php nginx phpmyadmin redis
docker compose exec php composer config --global audit.block-insecure false
docker compose exec php composer config --global process-timeout 0
docker compose exec php composer install --prefer-dist --no-progress
```

>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
Importante:

- O backend responde em `http://localhost:8181/api/v1`
- O login funciona em `POST http://localhost:8181/api/v1/sessoes/login`
<<<<<<< HEAD
- Health check (sem autenticacao): `GET http://localhost:8181/api/v1/health`
=======
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
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
<<<<<<< HEAD
- Health check: `http://localhost:8181/api/v1/health`
- phpMyAdmin: `http://localhost:8080`
- Frontend (Docker): `http://localhost:3000`
=======
- phpMyAdmin: `http://localhost:8080`
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929

## Banco de Dados e Seeds

Se o banco ainda nao estiver populado, rode os SQLs da pasta `backend/src/Utils/SQL` na ordem:

1. `00_SQL_criar_banco.sql`
2. `01_SQL_seed_dados_iniciais.sql`
3. `02_SQL_seed_dados_teste.sql`
<<<<<<< HEAD
4. `03_modulo_gestao_associacao.sql` — tabelas do modulo de gestao (membros, tarefas, historico)

Voce pode executar pelo phpMyAdmin ou importar via terminal:

```powershell
Get-Content ".\backend\src\Utils\SQL\03_modulo_gestao_associacao.sql" | docker exec -i hortas_mysql mysql -u hortas_user -phortas_password railway
```

Para verificar se as tabelas do modulo foram criadas:

```powershell
docker exec hortas_mysql mysql -u hortas_user -phortas_password railway -e "SHOW TABLES LIKE '%associacao%'; SHOW TABLES LIKE 'historico_participacao';"
```

Para listar os usuarios carregados:

```powershell
docker exec hortas_mysql mysql -u hortas_user -phortas_password railway -e "SELECT email, nome_completo, cpf FROM usuarios;"
=======

Voce pode executar pelo phpMyAdmin ou importar via terminal.

Para listar os usuarios carregados:

```bash
docker compose exec mysql mysql -u hortas_user -phortas_password railway -e "SELECT email, nome_completo, cpf FROM usuarios;"
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
```

## Login de Teste

Exemplo de teste:

<<<<<<< HEAD
```powershell
curl -X POST http://localhost:8181/api/v1/sessoes/login `
  -H "Content-Type: application/json" `
  -d "{\"email\":\"admin_assoc_1@example.com\",\"senha\":\"senha12345\"}"
```

Verificar disponibilidade da API:

```powershell
curl http://localhost:8181/api/v1/health
```

Resposta esperada quando tudo estiver ok:

```json
{
  "status": "ok",
  "database": "ok",
  "modulo": "associacao",
  "timestamp": "2026-05-24T12:00:00+00:00"
}
```

---

## Modulo de Gestao da Associacao

Implementacao focada nos requisitos funcionais e nao funcionais do modulo de associacao: controle de membros, tarefas, historico de participacao e acompanhamento de engajamento.

### Requisitos funcionais atendidos

| Funcionalidade | Descricao |
|---|---|
| Cadastro de membros | Nome, e-mail, telefone, status ativo/inativo |
| Gerenciamento de participantes | Edicao de dados e ativacao/inativacao |
| Criacao de tarefas | Tarefas vinculadas a uma associacao |
| Atribuicao de responsavel | Membro ativo pode ser designado para a tarefa |
| Conclusao de tarefas | Registra data de conclusao e gera historico |
| Historico de participacao | Registro automatico ao concluir tarefa com responsavel |
| Engajamento | Nivel calculado: alto (5+), medio (2-4), baixo (0-1), inativo |

### Requisitos nao funcionais atendidos

- **Usabilidade:** interface web simplificada em `/associacoes/:id/gestao` (frontend)
- **Confiabilidade:** validacao centralizada, soft delete e transacao ao concluir tarefa
- **Disponibilidade:** endpoint `/api/v1/health` verifica API e banco de dados
- **Manutencao:** camadas Model → Repository → Service → Controller

### Tabelas criadas

- `membros_associacao`
- `tarefas_associacao`
- `historico_participacao`

Script: [`backend/src/Utils/SQL/03_modulo_gestao_associacao.sql`](./src/Utils/SQL/03_modulo_gestao_associacao.sql)

### Endpoints da API

Todas as rotas abaixo exigem token JWT (header `Authorization: Bearer <token>`), exceto `/health`.

Base: `/api/v1/associacoes/{uuid}`

| Metodo | Rota | Descricao |
|---|---|---|
| GET | `/membros` | Listar membros |
| POST | `/membros` | Cadastrar membro |
| GET | `/membros/{membroUuid}` | Obter membro |
| PUT | `/membros/{membroUuid}` | Atualizar membro |
| DELETE | `/membros/{membroUuid}` | Excluir membro (soft delete) |
| GET | `/tarefas` | Listar tarefas |
| POST | `/tarefas` | Criar tarefa |
| PUT | `/tarefas/{tarefaUuid}` | Atualizar tarefa |
| POST | `/tarefas/{tarefaUuid}/concluir` | Concluir tarefa e registrar participacao |
| DELETE | `/tarefas/{tarefaUuid}` | Excluir tarefa (soft delete) |
| GET | `/historico-participacao` | Historico de participacao |
| GET | `/engajamento` | Nivel de engajamento por membro |

Exemplo — cadastrar membro:

```powershell
curl -X POST "http://localhost:8181/api/v1/associacoes/{UUID_DA_ASSOCIACAO}/membros" `
  -H "Authorization: Bearer {TOKEN}" `
  -H "Content-Type: application/json" `
  -d "{\"nome\":\"Maria Silva\",\"email\":\"maria@email.com\",\"status\":\"ativo\"}"
```

Exemplo — criar e concluir tarefa:

```powershell
# Criar tarefa
curl -X POST "http://localhost:8181/api/v1/associacoes/{UUID}/tarefas" `
  -H "Authorization: Bearer {TOKEN}" `
  -H "Content-Type: application/json" `
  -d "{\"titulo\":\"Regar canteiros\",\"membro_responsavel_uuid\":\"{UUID_MEMBRO}\"}"

# Concluir tarefa
curl -X POST "http://localhost:8181/api/v1/associacoes/{UUID}/tarefas/{UUID_TAREFA}/concluir" `
  -H "Authorization: Bearer {TOKEN}"
```

### Arquivos novos (backend)

- [`backend/src/Utils/SQL/03_modulo_gestao_associacao.sql`](./src/Utils/SQL/03_modulo_gestao_associacao.sql)
- [`backend/src/Utils/AssociacaoGestaoValidator.php`](./src/Utils/AssociacaoGestaoValidator.php)
- [`backend/src/Models/MembroAssociacaoModel.php`](./src/Models/MembroAssociacaoModel.php)
- [`backend/src/Models/TarefaAssociacaoModel.php`](./src/Models/TarefaAssociacaoModel.php)
- [`backend/src/Models/HistoricoParticipacaoModel.php`](./src/Models/HistoricoParticipacaoModel.php)
- [`backend/src/Repositories/MembroAssociacaoRepository.php`](./src/Repositories/MembroAssociacaoRepository.php)
- [`backend/src/Repositories/TarefaAssociacaoRepository.php`](./src/Repositories/TarefaAssociacaoRepository.php)
- [`backend/src/Repositories/HistoricoParticipacaoRepository.php`](./src/Repositories/HistoricoParticipacaoRepository.php)
- [`backend/src/Services/MembroAssociacaoService.php`](./src/Services/MembroAssociacaoService.php)
- [`backend/src/Services/TarefaAssociacaoService.php`](./src/Services/TarefaAssociacaoService.php)
- [`backend/src/Services/EngajamentoAssociacaoService.php`](./src/Services/EngajamentoAssociacaoService.php)
- [`backend/src/Controllers/MembroAssociacaoController.php`](./src/Controllers/MembroAssociacaoController.php)
- [`backend/src/Controllers/TarefaAssociacaoController.php`](./src/Controllers/TarefaAssociacaoController.php)
- [`backend/src/Controllers/EngajamentoAssociacaoController.php`](./src/Controllers/EngajamentoAssociacaoController.php)
- [`backend/src/Controllers/HealthController.php`](./src/Controllers/HealthController.php)
- [`backend/src/Routes/HealthRoutes.php`](./src/Routes/HealthRoutes.php)
- [`backend/config/associacao_gestao_bindings.php`](./config/associacao_gestao_bindings.php)
- [`backend/config/health_bindings.php`](./config/health_bindings.php)

### Arquivos atualizados (backend)

- [`backend/src/Routes/AssociacaoRoutes.php`](./src/Routes/AssociacaoRoutes.php)
- [`backend/src/Routes/IndexRoutes.php`](./src/Routes/IndexRoutes.php)
- [`backend/src/Middlewares/JwtMiddleware.php`](./src/Middlewares/JwtMiddleware.php) — rota `/health` publica
- [`backend/config/dependencies.php`](./config/dependencies.php)

### Frontend relacionado

A tela de gestao esta em `frontend/src/views/Associacoes/Gestao.vue`, acessivel em:

- `http://localhost:3000/associacoes` → botao **Gestao**

Arquivos principais:

- `frontend/src/services/associacaoGestao.service.js`
- `frontend/src/store/modules/associacaoGestao.js`
- `frontend/src/utils/mensagens.js`

---

## Melhorias de Autenticacao Aplicadas

O backend continua usando PHP-DI como container principal, mas a criacao de associacoes na autenticacao e autorizacao ficou mais centralizada.
=======
```bash
curl -X POST http://localhost:8181/api/v1/sessoes/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin_assoc_1@example.com\",\"senha\":\"senha12345\"}"
```

## Melhorias de Associações Aplicadas

O backend continua usando PHP-DI como container principal, mas agora a criacao de associações na autenticacao e autorizacao ficou mais centralizada.
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929

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
<<<<<<< HEAD
- Melhor legibilidade da arquitetura para documentacao
=======
- Melhor legibilidade da arquitetura para documentacao 
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929

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

<<<<<<< HEAD
```powershell
# Ver containers do projeto
docker ps --filter "name=hortas_"

# Iniciar containers existentes (sem recriar)
docker start hortas_mysql hortas_php hortas_nginx hortas_phpmyadmin hortas_redis hortas_frontend

# Executar SQL do modulo de gestao
Get-Content ".\backend\src\Utils\SQL\03_modulo_gestao_associacao.sql" | docker exec -i hortas_mysql mysql -u hortas_user -phortas_password railway

# Composer dentro do PHP
docker exec hortas_php composer install --prefer-dist --no-progress --working-dir=/var/www/backend

=======
```bash
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
# Parar todos os containers
docker ps -aq | ForEach-Object { docker stop $_ }

# Remover todos os containers
docker ps -aq | ForEach-Object { docker rm $_ }

# Remover todas as imagens
docker images -q | ForEach-Object { docker rmi -f $_ }
```
