-- Corrige textos com encoding quebrado (mojibake e caracteres perdidos)
SET NAMES utf8mb4;

-- Textos gravados com dupla codificacao UTF-8 (ex.: AssociaÃ§Ã£o)
UPDATE associacoes
SET razao_social = CONVERT(CAST(CONVERT(razao_social USING latin1) AS BINARY) USING utf8mb4)
WHERE HEX(razao_social) LIKE '%C383C2%';

UPDATE enderecos
SET cidade = CONVERT(CAST(CONVERT(cidade USING latin1) AS BINARY) USING utf8mb4)
WHERE HEX(cidade) LIKE '%C383C2%';

UPDATE enderecos
SET bairro = CONVERT(CAST(CONVERT(bairro USING latin1) AS BINARY) USING utf8mb4)
WHERE HEX(bairro) LIKE '%C383C2%';

UPDATE enderecos
SET logradouro = CONVERT(CAST(CONVERT(logradouro USING latin1) AS BINARY) USING utf8mb4)
WHERE HEX(logradouro) LIKE '%C383C2%';

-- Textos com caracteres perdidos na importacao (ex.: Administra????o)
UPDATE usuarios
SET nome_completo = 'Administração da Plataforma'
WHERE email = 'hortas_comunitarias@univille.br';

UPDATE cargos
SET nome = 'Administração da Plataforma',
    descricao = 'Usuário com acesso total ao sistema.'
WHERE slug = 'admin_plataforma';

UPDATE cargos
SET nome = 'Administração da Associação',
    descricao = 'Gerencia todas as associações.'
WHERE slug = 'admin_associacao_geral';

UPDATE cargos
SET nome = 'Administração da Horta',
    descricao = 'Gerencia todas as hortas.'
WHERE slug = 'admin_horta_geral';
