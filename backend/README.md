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

```powershell
copy backend\.env.example backend\.env
docker compose build php
docker compose up -d
docker start hortas_mysql hortas_php hortas_nginx hortas_phpmyadmin hortas_redis hortas_frontend
```

Instale as dependencias PHP dentro do container:

```powershell
docker exec hortas_php composer config --global audit.block-insecure false
docker exec hortas_php composer config --global process-timeout 0
docker exec hortas_php composer install --prefer-dist --no-progress --working-dir=/var/www/backend
```

> **Importante:** use `docker compose up -d` na pasta do projeto (`hortas-comunitarias-univille`). Evite apenas `docker start` em containers antigos criados em outro diretorio — isso pode apontar volumes errados e quebrar o localhost.

> **Nota:** Se `docker compose exec php` retornar `service "php" is not running`, use `docker exec hortas_php`.

Importante:

- O backend responde em `http://localhost:8181/api/v1`
- O login funciona em `POST http://localhost:8181/api/v1/sessoes/login`
- Health check (sem autenticacao): `GET http://localhost:8181/api/v1/health`
- Se existir conflito de configuracao no Nginx, mantenha desativado `docker/nginx/sites/ci.conf.disabled` no ambiente local

### Variaveis de Ambiente

Crie `backend/.env` com base em `backend/.env.example`.

Exemplo:
```env
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
- Health check: `http://localhost:8181/api/v1/health`
- phpMyAdmin: `http://localhost:8080`
- Frontend (Docker): `http://localhost:3000`

---

## Implementacoes Recentes (Frontend + Backend)

Resumo do que foi implementado e ajustado no ambiente local.

### Infraestrutura Docker

| Item | Descricao |
|------|-----------|
| `docker/php/Dockerfile` | Container PHP 8.2-FPM com extensoes `pdo_mysql`, `mysqli`, `zip` e Composer |
| `docker/php/php.ini` | Configuracao PHP para desenvolvimento |
| `docker-compose.yml` | Frontend sem instalacao global quebrada do Vue CLI; dependencias via `npm install` local |

### Modulo de Associacoes (Frontend)

Tela em `http://localhost:3000/associacoes`:

| Funcionalidade | Descricao |
|----------------|-----------|
| Listagem em tabela | CNPJ, nome, endereco legivel, administradores, status |
| Cards de resumo | Total, ativas e inativas/pendentes |
| Busca e filtro | Por CNPJ, nome, endereco ou status |
| Acoes por linha | Gestao (olho), Tarefas, Editar, Excluir |
| Cadastro com CNPJ | Campo obrigatorio com mascara e validacao de digitos |
| Edicao com CNPJ | Mesma validacao do cadastro |

Arquivos principais:

- `frontend/src/views/Associacoes/List.vue`
- `frontend/src/views/Associacoes/Create.vue`
- `frontend/src/views/Associacoes/Edit.vue`
- `frontend/src/utils/cnpj.js`

### Gestao da Associacao (Frontend)

Rota: `http://localhost:3000/associacoes/{uuid}/gestao`

Abas disponiveis:

| Aba | Conteudo |
|-----|----------|
| Visao Geral | Metricas (membros, tarefas, engajamento alto) e atalhos |
| Membros | Cadastro, ativacao/inativacao e exclusao |
| Tarefas | Criar, atribuir responsavel, concluir e excluir |
| Engajamento | Nivel por membro + historico de participacao |

Atalho direto para tarefas: `http://localhost:3000/associacoes/{uuid}/gestao?tab=tarefas`

Arquivos principais:

- `frontend/src/views/Associacoes/Gestao.vue`
- `frontend/src/services/associacaoGestao.service.js`
- `frontend/src/store/modules/associacaoGestao.js`

### Melhorias no Backend (Associacoes)

| Item | Arquivo | Descricao |
|------|---------|-----------|
| CNPJ obrigatorio | `src/Utils/CnpjValidator.php` | Normaliza, valida digitos e impede duplicados |
| Endereco legivel | `src/Utils/EnderecoFormatter.php` | Formata endereco a partir da tabela `enderecos` (sem UUID/JSON) |
| API enriquecida | `src/Controllers/AssociacaoController.php` | Retorna `cnpj`, `endereco`, `status`, `administradores` |
| Charset UTF-8 | `config/database.php` | Conexao `utf8mb4` + `SET NAMES utf8mb4` |
| Respostas JSON | `ForcarJsonMiddleware.php`, `JsonResponseFactory.php` | Header `charset=utf-8` e `JSON_UNESCAPED_UNICODE` |

### Encoding e acentuacao

Se aparecer `Administra????o` ou `AssociaÃ§Ã£o`:

1. Confirme `DB_CHARSET=utf8mb4` em `backend/.env`
2. Execute o script de correcao:

```powershell
docker cp ".\backend\src\Utils\SQL\04_fix_encoding_utf8.sql" hortas_mysql:/tmp/04_fix_encoding_utf8.sql
docker exec hortas_mysql mysql -u hortas_user -phortas_password --default-character-set=utf8mb4 railway -e "source /tmp/04_fix_encoding_utf8.sql"
```

3. Faca logout e login novamente no frontend (o nome fica em cache no `localStorage`)

---

## Banco de Dados e Seeds

Se o banco ainda nao estiver populado, rode os SQLs da pasta `backend/src/Utils/SQL` na ordem:

1. `00_SQL_criar_banco.sql`
2. `01_SQL_seed_dados_iniciais.sql`
3. `02_SQL_seed_dados_teste.sql`
4. `03_modulo_gestao_associacao.sql` — tabelas do modulo de gestao (membros, tarefas, historico)
5. `04_fix_encoding_utf8.sql` — corrige acentuacao se os seeds foram importados com encoding errado no Windows

> Se `00_SQL_criar_banco.sql` retornar `Table already exists`, o banco ja tem tabelas — pule o passo 00 e rode apenas `01` a `04`.

Voce pode executar pelo phpMyAdmin ou importar via terminal.

**No Windows (PowerShell), use sempre UTF-8 ao importar SQL:**

```powershell
Get-Content ".\backend\src\Utils\SQL\01_SQL_seed_dados_iniciais.sql" -Encoding UTF8 | docker exec -i hortas_mysql mysql -u hortas_user -phortas_password --default-character-set=utf8mb4 railway
```

Ou copie o arquivo para o container e execute com `source`:

```powershell
docker cp ".\backend\src\Utils\SQL\04_fix_encoding_utf8.sql" hortas_mysql:/tmp/04_fix_encoding_utf8.sql
docker exec hortas_mysql mysql -u hortas_user -phortas_password --default-character-set=utf8mb4 railway -e "source /tmp/04_fix_encoding_utf8.sql"
```

Exemplo para o modulo de gestao:

```powershell
Get-Content ".\backend\src\Utils\SQL\03_modulo_gestao_associacao.sql" -Encoding UTF8 | docker exec -i hortas_mysql mysql -u hortas_user -phortas_password --default-character-set=utf8mb4 railway
```

Para verificar se as tabelas do modulo foram criadas:

```powershell
docker exec hortas_mysql mysql -u hortas_user -phortas_password railway -e "SHOW TABLES LIKE '%associacao%'; SHOW TABLES LIKE 'historico_participacao';"
```

Para listar os usuarios carregados:

```powershell
docker exec hortas_mysql mysql -u hortas_user -phortas_password railway -e "SELECT email, nome_completo, cpf FROM usuarios;"
```

## Credenciais de Teste

### Administrador da plataforma (seed padrao)

| Campo | Valor |
|-------|-------|
| Email | `hortas_comunitarias@univille.br` |
| Senha | `senha12345` |

### Outros usuarios de teste (apos seed `02`)

| Perfil | Email | Senha |
|--------|-------|-------|
| Admin associacao | `admin_assoc_1@example.com` | `senha12345` |
| Canteirista | `canteirista_1@example.com` | `senha12345` |

Login no frontend: `http://localhost:3000` → selecione **Administrador** ou **Canteirista**.

## Login de Teste (API)

Exemplo de teste:

```powershell
curl -X POST http://localhost:8181/api/v1/sessoes/login `
  -H "Content-Type: application/json" `
  -d "{\"email\":\"hortas_comunitarias@univille.br\",\"senha\":\"senha12345\"}"
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
- [`backend/src/Utils/SQL/04_fix_encoding_utf8.sql`](./src/Utils/SQL/04_fix_encoding_utf8.sql)
- [`backend/src/Utils/CnpjValidator.php`](./src/Utils/CnpjValidator.php)
- [`backend/src/Utils/EnderecoFormatter.php`](./src/Utils/EnderecoFormatter.php)
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

- [`backend/src/Controllers/AssociacaoController.php`](./src/Controllers/AssociacaoController.php) — resposta com CNPJ, endereco e status
- [`backend/src/Services/AssociacaoService.php`](./src/Services/AssociacaoService.php) — validacao de CNPJ
- [`backend/src/Repositories/AssociacaoRepository.php`](./src/Repositories/AssociacaoRepository.php) — eager load de endereco
- [`backend/config/database.php`](./config/database.php) — charset utf8mb4
- [`backend/src/Routes/AssociacaoRoutes.php`](./src/Routes/AssociacaoRoutes.php)
- [`backend/src/Routes/IndexRoutes.php`](./src/Routes/IndexRoutes.php)
- [`backend/src/Middlewares/JwtMiddleware.php`](./src/Middlewares/JwtMiddleware.php) — rota `/health` publica
- [`backend/src/Middlewares/ForcarJsonMiddleware.php`](./src/Middlewares/ForcarJsonMiddleware.php)
- [`backend/config/dependencies.php`](./config/dependencies.php)

### Frontend relacionado

#### Listagem de associacoes

- URL: `http://localhost:3000/associacoes`
- Botao **Gestao** (icone de olho) abre a tela completa
- Botao **Tarefas** abre direto na aba de tarefas

#### Gestao por associacao

- URL: `http://localhost:3000/associacoes/{uuid}/gestao`
- Abas: Visao Geral, Membros, Tarefas, Engajamento

Arquivos principais:

- `frontend/src/views/Associacoes/List.vue`
- `frontend/src/views/Associacoes/Gestao.vue`
- `frontend/src/views/Associacoes/Create.vue`
- `frontend/src/views/Associacoes/Edit.vue`
- `frontend/src/services/associacaoGestao.service.js`
- `frontend/src/store/modules/associacaoGestao.js`
- `frontend/src/utils/cnpj.js`
- `frontend/src/utils/mensagens.js`

#### Cadastro de associacao com CNPJ

- URL: `http://localhost:3000/associacoes/criar`
- CNPJ obrigatorio, com mascara `00.000.000/0000-00` e validacao
- Exemplo de CNPJ valido para teste: `11.444.777/0001-61`

---

## Melhorias de Autenticacao Aplicadas

O backend continua usando PHP-DI como container principal, mas a criacao de associacoes na autenticacao e autorizacao ficou mais centralizada.

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
- Melhor legibilidade da arquitetura para documentacao

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

## Solucao de Problemas Comuns

### Localhost nao abre / 502 Bad Gateway

```powershell
# Recriar containers a partir da pasta correta do projeto
docker compose down
docker compose build php
docker compose up -d
docker exec hortas_php composer install --prefer-dist --no-progress --working-dir=/var/www/backend
```

Verifique: `curl http://localhost:8181/api/v1/health`

### Erro `Table 'enderecos' already exists` ao rodar `00_SQL_criar_banco.sql`

O banco ja tem tabelas parciais. **Pule o passo 00** e rode apenas os seeds (`01`, `02`, `03`). Se faltar a tabela `usuarios`, crie-a manualmente ou importe somente as tabelas ausentes.

### Frontend nao carrega (`vue-cli-service not found`)

```powershell
docker compose restart frontend
docker logs hortas_frontend --tail 30
```

Na primeira subida o container instala dependencias — aguarde alguns minutos.

### Acentos quebrados no navegador

Execute `04_fix_encoding_utf8.sql` (ver secao **Encoding e acentuacao**) e faca logout/login.

### CNPJ rejeitado no cadastro

O CNPJ precisa ser valido (14 digitos + verificadores). CNPJs ficticios com digitos repetidos (ex.: `11.111.111/1111-11`) sao rejeitados. Use um CNPJ de teste valido, como `11.444.777/0001-61`.

---

## Comandos Uteis do Docker

```powershell
# Ver containers do projeto
docker ps --filter "name=hortas_"

# Rebuild apenas do PHP apos mudancas no Dockerfile
docker compose build php
docker compose up -d php

# Reiniciar frontend apos mudancas no codigo Vue
docker compose restart frontend

# Executar SQL com encoding correto (Windows)
Get-Content ".\backend\src\Utils\SQL\03_modulo_gestao_associacao.sql" -Encoding UTF8 | docker exec -i hortas_mysql mysql -u hortas_user -phortas_password --default-character-set=utf8mb4 railway

# Composer dentro do PHP
docker exec hortas_php composer install --prefer-dist --no-progress --working-dir=/var/www/backend

# Ver logs
docker logs hortas_php --tail 30
docker logs hortas_frontend --tail 30
docker logs hortas_nginx --tail 30

# Parar todos os containers do projeto
docker compose down
```
