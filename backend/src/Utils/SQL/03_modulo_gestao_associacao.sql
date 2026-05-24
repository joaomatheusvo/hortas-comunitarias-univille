-- Módulo de gestão da associação: membros, tarefas e participação
-- Executar após 00_SQL_criar_banco.sql

USE railway;

CREATE TABLE IF NOT EXISTS membros_associacao (
    uuid CHAR(36) NOT NULL,
    associacao_uuid CHAR(36) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    telefone VARCHAR(30),
    observacoes TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'ativo',
    excluido TINYINT NOT NULL DEFAULT 0,
    usuario_criador_uuid CHAR(36),
    data_de_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_alterador_uuid CHAR(36),
    data_de_ultima_alteracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (uuid),
    FOREIGN KEY (associacao_uuid) REFERENCES associacoes(uuid)
);

CREATE TABLE IF NOT EXISTS tarefas_associacao (
    uuid CHAR(36) NOT NULL,
    associacao_uuid CHAR(36) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    membro_responsavel_uuid CHAR(36),
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    data_conclusao TIMESTAMP NULL,
    excluido TINYINT NOT NULL DEFAULT 0,
    usuario_criador_uuid CHAR(36),
    data_de_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_alterador_uuid CHAR(36),
    data_de_ultima_alteracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (uuid),
    FOREIGN KEY (associacao_uuid) REFERENCES associacoes(uuid),
    FOREIGN KEY (membro_responsavel_uuid) REFERENCES membros_associacao(uuid)
);

CREATE TABLE IF NOT EXISTS historico_participacao (
    uuid CHAR(36) NOT NULL,
    associacao_uuid CHAR(36) NOT NULL,
    membro_uuid CHAR(36) NOT NULL,
    tarefa_uuid CHAR(36),
    descricao TEXT NOT NULL,
    data_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    excluido TINYINT NOT NULL DEFAULT 0,
    usuario_criador_uuid CHAR(36),
    PRIMARY KEY (uuid),
    FOREIGN KEY (associacao_uuid) REFERENCES associacoes(uuid),
    FOREIGN KEY (membro_uuid) REFERENCES membros_associacao(uuid),
    FOREIGN KEY (tarefa_uuid) REFERENCES tarefas_associacao(uuid)
);
