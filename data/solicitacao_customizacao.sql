-- Solicitação de customização (tabelas + seed diretor)

CREATE TABLE IF NOT EXISTS tb_solicitacao_customizacao (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    fk_usuario_solicitante INT NULL,
    nome VARCHAR(150) NOT NULL,
    empresa VARCHAR(150) NULL,
    cargo VARCHAR(120) NULL,
    email VARCHAR(150) NULL,
    telefone VARCHAR(50) NULL,
    data_solicitacao DATE NULL,
    modulo_outro VARCHAR(150) NULL,
    descricao TEXT NULL,
    problema_atual TEXT NULL,
    resultado_esperado TEXT NULL,
    impacto_nivel VARCHAR(20) NULL,
    descricao_impacto TEXT NULL,
    prioridade VARCHAR(20) NULL,
    prazo_desejado DATE NULL,
    responsavel VARCHAR(150) NULL,
    assinatura VARCHAR(150) NULL,
    data_aprovacao DATE NULL,
    prazo_resposta VARCHAR(100) NULL,
    precificacao TEXT NULL,
    observacoes_resposta TEXT NULL,
    aprovacao_resposta VARCHAR(150) NULL,
    data_resposta DATE NULL,
    aprovacao_conex VARCHAR(10) NULL,
    data_aprovacao_conex DATE NULL,
    responsavel_aprovacao_conex VARCHAR(150) NULL,
    status VARCHAR(30) DEFAULT 'Aberto',
    resolvido_em DATETIME NULL,
    resolvido_por INT NULL,
    versao_sistema VARCHAR(50) NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitacao_usuario_solicitante FOREIGN KEY (fk_usuario_solicitante) REFERENCES tb_user(id_usuario)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_solicitacao_usuario_resolvido FOREIGN KEY (resolvido_por) REFERENCES tb_user(id_usuario)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tb_solicitacao_customizacao_modulo (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    fk_solicitacao INT NOT NULL,
    modulo VARCHAR(80) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitacao_modulo FOREIGN KEY (fk_solicitacao) REFERENCES tb_solicitacao_customizacao(id_solicitacao)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tb_solicitacao_customizacao_tipo (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    fk_solicitacao INT NOT NULL,
    tipo VARCHAR(120) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitacao_tipo FOREIGN KEY (fk_solicitacao) REFERENCES tb_solicitacao_customizacao(id_solicitacao)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tb_solicitacao_customizacao_anexo (
    id_anexo INT AUTO_INCREMENT PRIMARY KEY,
    fk_solicitacao INT NOT NULL,
    caminho_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NULL,
    mime VARCHAR(100) NULL,
    tamanho INT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitacao_anexo FOREIGN KEY (fk_solicitacao) REFERENCES tb_solicitacao_customizacao(id_solicitacao)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Colunas novas para aprovação Conex (caso a tabela já exista)
-- Se já existir, rode apenas os ADD COLUMN que estiverem faltando:
-- ALTER TABLE tb_solicitacao_customizacao ADD COLUMN aprovacao_conex VARCHAR(10) NULL;
-- ALTER TABLE tb_solicitacao_customizacao ADD COLUMN data_aprovacao_conex DATE NULL;
-- ALTER TABLE tb_solicitacao_customizacao ADD COLUMN responsavel_aprovacao_conex VARCHAR(150) NULL;

-- Usuario diretor (login: diretor@fullcare.com.br | senha: Fullcare2026)
INSERT INTO tb_user (
    usuario_user,
    login_user,
    email_user,
    senha_user,
    senha_default_user,
    ativo_user,
    nivel_user,
    cargo_user,
    data_create_user
)
SELECT
    'Diretor FullCare',
    'diretor@fullcare.com.br',
    'diretor@fullcare.com.br',
    '$2y$10$rb0S1xyMQra9Sa.eYugXkOyglxgBrtynJzTUO6z5501GRUEdLB11G',
    'Fullcare2026',
    's',
    -1,
    'Diretor',
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tb_user WHERE email_user = 'diretor@fullcare.com.br'
);
