-- Migration Aula 06 - AtendeLab

ALTER TABLE pessoas
    ADD COLUMN IF NOT EXISTS documento VARCHAR(20) NULL AFTER nome,
    ADD COLUMN IF NOT EXISTS curso VARCHAR(100) NULL AFTER email,
    ADD COLUMN IF NOT EXISTS periodo VARCHAR(20) NULL AFTER curso,
    ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER periodo,
    ADD COLUMN IF NOT EXISTS status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo' AFTER observacoes;

ALTER TABLE tipos_atendimentos
    ADD COLUMN IF NOT EXISTS nome VARCHAR(100) NULL AFTER id;

UPDATE tipos_atendimentos SET nome = descricao WHERE nome IS NULL OR nome = '';

ALTER TABLE tipos_atendimentos MODIFY COLUMN nome VARCHAR(100) NOT NULL;

ALTER TABLE atendimentos
    ADD COLUMN IF NOT EXISTS descricao TEXT NULL AFTER data_atendimento,
    ADD COLUMN IF NOT EXISTS horario_atendimento TIME NULL AFTER descricao,
    ADD COLUMN IF NOT EXISTS observacao_final TEXT NULL AFTER horario_atendimento;

ALTER TABLE atendimentos
    MODIFY COLUMN status ENUM('aberto', 'em_andamento', 'concluido', 'finalizado', 'cancelado') NOT NULL DEFAULT 'aberto';