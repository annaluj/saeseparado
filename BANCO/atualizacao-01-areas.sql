-- ============================================================
-- Atualização 01: áreas do profissional e do administrador
-- Rode UMA VEZ no banco "sae" (phpMyAdmin > banco sae > aba SQL > colar e executar).
-- ============================================================

-- Tipo de conta de cada usuário
ALTER TABLE usuarios
    ADD COLUMN tipo ENUM('estudante', 'profissional', 'admin') NOT NULL DEFAULT 'estudante' AFTER senha_hash;

-- Situação de cada agendamento e qual profissional vai atender
ALTER TABLE agendamentos
    ADD COLUMN status ENUM('pendente', 'confirmado', 'recusado', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente' AFTER horario_consulta,
    ADD COLUMN profissional_id INT(11) NULL DEFAULT NULL AFTER status,
    ADD CONSTRAINT fk_agendamento_profissional
        FOREIGN KEY (profissional_id) REFERENCES usuarios (usuario_id)
        ON DELETE SET NULL ON UPDATE CASCADE;

-- Deixa mais rápidas as buscas de pedidos e da agenda dos profissionais
CREATE INDEX idx_agendamento_status_data ON agendamentos (status, data_consulta, horario_consulta);

-- ------------------------------------------------------------
-- PRIMEIRO ADMINISTRADOR: troque o e-mail pelo da conta que vai ser admin
-- (a conta precisa já estar cadastrada no site) e rode esta linha:
--
-- UPDATE usuarios SET tipo = 'admin' WHERE email = 'email-da-conta@exemplo.com';
--
-- Depois disso, o administrador transforma outras contas em profissional pelo próprio site.
-- ------------------------------------------------------------
