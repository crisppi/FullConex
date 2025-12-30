START TRANSACTION;

-- Ajustes na tabela de log (tb_log_historico)
ALTER TABLE tb_log_historico
  ADD COLUMN IF NOT EXISTS usuario_id INT NULL AFTER email_user,
  ADD COLUMN IF NOT EXISTS usuario_nome VARCHAR(255) NULL AFTER usuario_id,
  ADD COLUMN IF NOT EXISTS ip VARCHAR(45) NULL AFTER usuario_nome,
  ADD COLUMN IF NOT EXISTS user_agent VARCHAR(512) NULL AFTER ip,
  ADD COLUMN IF NOT EXISTS created_at DATETIME NULL AFTER user_agent;

-- Triggers para tb_acomodacao
DROP TRIGGER IF EXISTS trg_log_insert_tb_acomodacao;
DROP TRIGGER IF EXISTS trg_log_update_tb_acomodacao;
DROP TRIGGER IF EXISTS trg_log_delete_tb_acomodacao;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_acomodacao
AFTER INSERT ON tb_acomodacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_acomodacao', 'INSERT', NOW(), NEW.`id_acomodacao`, NULL, JSON_OBJECT('id_acomodacao', NEW.`id_acomodacao`, 'acomodacao_aco', NEW.`acomodacao_aco`, 'valor_aco', NEW.`valor_aco`, 'fk_hospital', NEW.`fk_hospital`, 'fk_usuario_acomodacao', NEW.`fk_usuario_acomodacao`, 'usuario_create_acomodacao', NEW.`usuario_create_acomodacao`, 'data_create_acomodacao', NEW.`data_create_acomodacao`, 'deletado_aco', NEW.`deletado_aco`, 'data_contrato_aco', NEW.`data_contrato_aco`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_acomodacao
AFTER UPDATE ON tb_acomodacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_acomodacao', 'UPDATE', NOW(), NEW.`id_acomodacao`, JSON_OBJECT('id_acomodacao', OLD.`id_acomodacao`, 'acomodacao_aco', OLD.`acomodacao_aco`, 'valor_aco', OLD.`valor_aco`, 'fk_hospital', OLD.`fk_hospital`, 'fk_usuario_acomodacao', OLD.`fk_usuario_acomodacao`, 'usuario_create_acomodacao', OLD.`usuario_create_acomodacao`, 'data_create_acomodacao', OLD.`data_create_acomodacao`, 'deletado_aco', OLD.`deletado_aco`, 'data_contrato_aco', OLD.`data_contrato_aco`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_acomodacao', NEW.`id_acomodacao`, 'acomodacao_aco', NEW.`acomodacao_aco`, 'valor_aco', NEW.`valor_aco`, 'fk_hospital', NEW.`fk_hospital`, 'fk_usuario_acomodacao', NEW.`fk_usuario_acomodacao`, 'usuario_create_acomodacao', NEW.`usuario_create_acomodacao`, 'data_create_acomodacao', NEW.`data_create_acomodacao`, 'deletado_aco', NEW.`deletado_aco`, 'data_contrato_aco', NEW.`data_contrato_aco`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_acomodacao
AFTER DELETE ON tb_acomodacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_acomodacao', 'DELETE', NOW(), OLD.`id_acomodacao`, JSON_OBJECT('id_acomodacao', OLD.`id_acomodacao`, 'acomodacao_aco', OLD.`acomodacao_aco`, 'valor_aco', OLD.`valor_aco`, 'fk_hospital', OLD.`fk_hospital`, 'fk_usuario_acomodacao', OLD.`fk_usuario_acomodacao`, 'usuario_create_acomodacao', OLD.`usuario_create_acomodacao`, 'data_create_acomodacao', OLD.`data_create_acomodacao`, 'deletado_aco', OLD.`deletado_aco`, 'data_contrato_aco', OLD.`data_contrato_aco`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_alta
DROP TRIGGER IF EXISTS trg_log_insert_tb_alta;
DROP TRIGGER IF EXISTS trg_log_update_tb_alta;
DROP TRIGGER IF EXISTS trg_log_delete_tb_alta;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_alta
AFTER INSERT ON tb_alta
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_alta', 'INSERT', NOW(), NEW.`id_alta`, NULL, JSON_OBJECT('id_alta', NEW.`id_alta`, 'fk_id_int_alt', NEW.`fk_id_int_alt`, 'tipo_alta_alt', NEW.`tipo_alta_alt`, 'data_alta_alt', NEW.`data_alta_alt`, 'hora_alta_alt', NEW.`hora_alta_alt`, 'internado_alt', NEW.`internado_alt`, 'usuario_alt', NEW.`usuario_alt`, 'data_create_alt', NEW.`data_create_alt`, 'fk_usuario_alt', NEW.`fk_usuario_alt`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_alta
AFTER UPDATE ON tb_alta
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_alta', 'UPDATE', NOW(), NEW.`id_alta`, JSON_OBJECT('id_alta', OLD.`id_alta`, 'fk_id_int_alt', OLD.`fk_id_int_alt`, 'tipo_alta_alt', OLD.`tipo_alta_alt`, 'data_alta_alt', OLD.`data_alta_alt`, 'hora_alta_alt', OLD.`hora_alta_alt`, 'internado_alt', OLD.`internado_alt`, 'usuario_alt', OLD.`usuario_alt`, 'data_create_alt', OLD.`data_create_alt`, 'fk_usuario_alt', OLD.`fk_usuario_alt`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_alta', NEW.`id_alta`, 'fk_id_int_alt', NEW.`fk_id_int_alt`, 'tipo_alta_alt', NEW.`tipo_alta_alt`, 'data_alta_alt', NEW.`data_alta_alt`, 'hora_alta_alt', NEW.`hora_alta_alt`, 'internado_alt', NEW.`internado_alt`, 'usuario_alt', NEW.`usuario_alt`, 'data_create_alt', NEW.`data_create_alt`, 'fk_usuario_alt', NEW.`fk_usuario_alt`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_alta
AFTER DELETE ON tb_alta
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_alta', 'DELETE', NOW(), OLD.`id_alta`, JSON_OBJECT('id_alta', OLD.`id_alta`, 'fk_id_int_alt', OLD.`fk_id_int_alt`, 'tipo_alta_alt', OLD.`tipo_alta_alt`, 'data_alta_alt', OLD.`data_alta_alt`, 'hora_alta_alt', OLD.`hora_alta_alt`, 'internado_alt', OLD.`internado_alt`, 'usuario_alt', OLD.`usuario_alt`, 'data_create_alt', OLD.`data_create_alt`, 'fk_usuario_alt', OLD.`fk_usuario_alt`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_antecedente
DROP TRIGGER IF EXISTS trg_log_insert_tb_antecedente;
DROP TRIGGER IF EXISTS trg_log_update_tb_antecedente;
DROP TRIGGER IF EXISTS trg_log_delete_tb_antecedente;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_antecedente
AFTER INSERT ON tb_antecedente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_antecedente', 'INSERT', NOW(), NEW.`id_antecedente`, NULL, JSON_OBJECT('id_antecedente', NEW.`id_antecedente`, 'antecedente_ant', NEW.`antecedente_ant`, 'fk_usuario_ant', NEW.`fk_usuario_ant`, 'usuario_create_ant', NEW.`usuario_create_ant`, 'data_create_ant', NEW.`data_create_ant`, 'fk_cid_10_ant', NEW.`fk_cid_10_ant`, 'deletado_ant', NEW.`deletado_ant`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_antecedente
AFTER UPDATE ON tb_antecedente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_antecedente', 'UPDATE', NOW(), NEW.`id_antecedente`, JSON_OBJECT('id_antecedente', OLD.`id_antecedente`, 'antecedente_ant', OLD.`antecedente_ant`, 'fk_usuario_ant', OLD.`fk_usuario_ant`, 'usuario_create_ant', OLD.`usuario_create_ant`, 'data_create_ant', OLD.`data_create_ant`, 'fk_cid_10_ant', OLD.`fk_cid_10_ant`, 'deletado_ant', OLD.`deletado_ant`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_antecedente', NEW.`id_antecedente`, 'antecedente_ant', NEW.`antecedente_ant`, 'fk_usuario_ant', NEW.`fk_usuario_ant`, 'usuario_create_ant', NEW.`usuario_create_ant`, 'data_create_ant', NEW.`data_create_ant`, 'fk_cid_10_ant', NEW.`fk_cid_10_ant`, 'deletado_ant', NEW.`deletado_ant`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_antecedente
AFTER DELETE ON tb_antecedente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_antecedente', 'DELETE', NOW(), OLD.`id_antecedente`, JSON_OBJECT('id_antecedente', OLD.`id_antecedente`, 'antecedente_ant', OLD.`antecedente_ant`, 'fk_usuario_ant', OLD.`fk_usuario_ant`, 'usuario_create_ant', OLD.`usuario_create_ant`, 'data_create_ant', OLD.`data_create_ant`, 'fk_cid_10_ant', OLD.`fk_cid_10_ant`, 'deletado_ant', OLD.`deletado_ant`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_capeante
DROP TRIGGER IF EXISTS trg_log_insert_tb_capeante;
DROP TRIGGER IF EXISTS trg_log_update_tb_capeante;
DROP TRIGGER IF EXISTS trg_log_delete_tb_capeante;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_capeante
AFTER INSERT ON tb_capeante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_capeante', 'INSERT', NOW(), NEW.`id_capeante`, NULL, JSON_OBJECT('id_capeante', NEW.`id_capeante`, 'fk_int_capeante', NEW.`fk_int_capeante`, 'adm_check', NEW.`adm_check`, 'aud_adm_capeante', NEW.`aud_adm_capeante`, 'aud_enf_capeante', NEW.`aud_enf_capeante`, 'aud_med_capeante', NEW.`aud_med_capeante`, 'data_fech_capeante', NEW.`data_fech_capeante`, 'data_digit_capeante', NEW.`data_digit_capeante`, 'data_final_capeante', NEW.`data_final_capeante`, 'data_inicial_capeante', NEW.`data_inicial_capeante`, 'diarias_capeante', NEW.`diarias_capeante`, 'enfer_check', NEW.`enfer_check`, 'glosa_diaria', NEW.`glosa_diaria`, 'glosa_honorarios', NEW.`glosa_honorarios`, 'glosa_matmed', NEW.`glosa_matmed`, 'glosa_oxig', NEW.`glosa_oxig`, 'glosa_sadt', NEW.`glosa_sadt`, 'glosa_taxas', NEW.`glosa_taxas`, 'glosa_opme', NEW.`glosa_opme`, 'med_check', NEW.`med_check`, 'pacote', NEW.`pacote`, 'parcial_capeante', NEW.`parcial_capeante`, 'parcial_num', NEW.`parcial_num`, 'valor_apresentado_capeante', NEW.`valor_apresentado_capeante`, 'valor_diarias', NEW.`valor_diarias`, 'valor_final_capeante', NEW.`valor_final_capeante`, 'valor_glosa_enf', NEW.`valor_glosa_enf`, 'valor_glosa_med', NEW.`valor_glosa_med`, 'valor_glosa_total', NEW.`valor_glosa_total`, 'valor_honorarios', NEW.`valor_honorarios`, 'valor_matmed', NEW.`valor_matmed`, 'valor_medicamentos', NEW.`valor_medicamentos`, 'valor_materiais', NEW.`valor_materiais`, 'glosa_materiais', NEW.`glosa_materiais`, 'glosa_medicamentos', NEW.`glosa_medicamentos`, 'valor_oxig', NEW.`valor_oxig`, 'valor_sadt', NEW.`valor_sadt`, 'valor_taxa', NEW.`valor_taxa`, 'valor_opme', NEW.`valor_opme`, 'fk_user_cap', NEW.`fk_user_cap`, 'aberto_cap', NEW.`aberto_cap`, 'encerrado_cap', NEW.`encerrado_cap`, 'glosa_total', NEW.`glosa_total`, 'desconto_valor_cap', NEW.`desconto_valor_cap`, 'negociado_desconto_cap', NEW.`negociado_desconto_cap`, 'protocolo_cap', NEW.`protocolo_cap`, 'em_auditoria_cap', NEW.`em_auditoria_cap`, 'senha_finalizada', NEW.`senha_finalizada`, 'usuario_create_cap', NEW.`usuario_create_cap`, 'data_create_cap', NEW.`data_create_cap`, 'deletado_cap', NEW.`deletado_cap`, 'conta_parada_cap', NEW.`conta_parada_cap`, 'parada_motivo_cap', NEW.`parada_motivo_cap`, 'impresso_cap', NEW.`impresso_cap`, 'fk_id_aud_enf', NEW.`fk_id_aud_enf`, 'fk_id_aud_med', NEW.`fk_id_aud_med`, 'fk_id_aud_adm', NEW.`fk_id_aud_adm`, 'fk_id_aud_hosp', NEW.`fk_id_aud_hosp`, 'validacao_cap', NEW.`validacao_cap`, 'lote_cap', NEW.`lote_cap`, 'updated_at', NEW.`updated_at`, 'conta_faturada_cap', NEW.`conta_faturada_cap`, 'conta_fatura_cap', NEW.`conta_fatura_cap`, 'acomodacao_cap', NEW.`acomodacao_cap`, 'adm_capeante', NEW.`adm_capeante`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_capeante
AFTER UPDATE ON tb_capeante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_capeante', 'UPDATE', NOW(), NEW.`id_capeante`, JSON_OBJECT('id_capeante', OLD.`id_capeante`, 'fk_int_capeante', OLD.`fk_int_capeante`, 'adm_check', OLD.`adm_check`, 'aud_adm_capeante', OLD.`aud_adm_capeante`, 'aud_enf_capeante', OLD.`aud_enf_capeante`, 'aud_med_capeante', OLD.`aud_med_capeante`, 'data_fech_capeante', OLD.`data_fech_capeante`, 'data_digit_capeante', OLD.`data_digit_capeante`, 'data_final_capeante', OLD.`data_final_capeante`, 'data_inicial_capeante', OLD.`data_inicial_capeante`, 'diarias_capeante', OLD.`diarias_capeante`, 'enfer_check', OLD.`enfer_check`, 'glosa_diaria', OLD.`glosa_diaria`, 'glosa_honorarios', OLD.`glosa_honorarios`, 'glosa_matmed', OLD.`glosa_matmed`, 'glosa_oxig', OLD.`glosa_oxig`, 'glosa_sadt', OLD.`glosa_sadt`, 'glosa_taxas', OLD.`glosa_taxas`, 'glosa_opme', OLD.`glosa_opme`, 'med_check', OLD.`med_check`, 'pacote', OLD.`pacote`, 'parcial_capeante', OLD.`parcial_capeante`, 'parcial_num', OLD.`parcial_num`, 'valor_apresentado_capeante', OLD.`valor_apresentado_capeante`, 'valor_diarias', OLD.`valor_diarias`, 'valor_final_capeante', OLD.`valor_final_capeante`, 'valor_glosa_enf', OLD.`valor_glosa_enf`, 'valor_glosa_med', OLD.`valor_glosa_med`, 'valor_glosa_total', OLD.`valor_glosa_total`, 'valor_honorarios', OLD.`valor_honorarios`, 'valor_matmed', OLD.`valor_matmed`, 'valor_medicamentos', OLD.`valor_medicamentos`, 'valor_materiais', OLD.`valor_materiais`, 'glosa_materiais', OLD.`glosa_materiais`, 'glosa_medicamentos', OLD.`glosa_medicamentos`, 'valor_oxig', OLD.`valor_oxig`, 'valor_sadt', OLD.`valor_sadt`, 'valor_taxa', OLD.`valor_taxa`, 'valor_opme', OLD.`valor_opme`, 'fk_user_cap', OLD.`fk_user_cap`, 'aberto_cap', OLD.`aberto_cap`, 'encerrado_cap', OLD.`encerrado_cap`, 'glosa_total', OLD.`glosa_total`, 'desconto_valor_cap', OLD.`desconto_valor_cap`, 'negociado_desconto_cap', OLD.`negociado_desconto_cap`, 'protocolo_cap', OLD.`protocolo_cap`, 'em_auditoria_cap', OLD.`em_auditoria_cap`, 'senha_finalizada', OLD.`senha_finalizada`, 'usuario_create_cap', OLD.`usuario_create_cap`, 'data_create_cap', OLD.`data_create_cap`, 'deletado_cap', OLD.`deletado_cap`, 'conta_parada_cap', OLD.`conta_parada_cap`, 'parada_motivo_cap', OLD.`parada_motivo_cap`, 'impresso_cap', OLD.`impresso_cap`, 'fk_id_aud_enf', OLD.`fk_id_aud_enf`, 'fk_id_aud_med', OLD.`fk_id_aud_med`, 'fk_id_aud_adm', OLD.`fk_id_aud_adm`, 'fk_id_aud_hosp', OLD.`fk_id_aud_hosp`, 'validacao_cap', OLD.`validacao_cap`, 'lote_cap', OLD.`lote_cap`, 'updated_at', OLD.`updated_at`, 'conta_faturada_cap', OLD.`conta_faturada_cap`, 'conta_fatura_cap', OLD.`conta_fatura_cap`, 'acomodacao_cap', OLD.`acomodacao_cap`, 'adm_capeante', OLD.`adm_capeante`), JSON_OBJECT('id_capeante', NEW.`id_capeante`, 'fk_int_capeante', NEW.`fk_int_capeante`, 'adm_check', NEW.`adm_check`, 'aud_adm_capeante', NEW.`aud_adm_capeante`, 'aud_enf_capeante', NEW.`aud_enf_capeante`, 'aud_med_capeante', NEW.`aud_med_capeante`, 'data_fech_capeante', NEW.`data_fech_capeante`, 'data_digit_capeante', NEW.`data_digit_capeante`, 'data_final_capeante', NEW.`data_final_capeante`, 'data_inicial_capeante', NEW.`data_inicial_capeante`, 'diarias_capeante', NEW.`diarias_capeante`, 'enfer_check', NEW.`enfer_check`, 'glosa_diaria', NEW.`glosa_diaria`, 'glosa_honorarios', NEW.`glosa_honorarios`, 'glosa_matmed', NEW.`glosa_matmed`, 'glosa_oxig', NEW.`glosa_oxig`, 'glosa_sadt', NEW.`glosa_sadt`, 'glosa_taxas', NEW.`glosa_taxas`, 'glosa_opme', NEW.`glosa_opme`, 'med_check', NEW.`med_check`, 'pacote', NEW.`pacote`, 'parcial_capeante', NEW.`parcial_capeante`, 'parcial_num', NEW.`parcial_num`, 'valor_apresentado_capeante', NEW.`valor_apresentado_capeante`, 'valor_diarias', NEW.`valor_diarias`, 'valor_final_capeante', NEW.`valor_final_capeante`, 'valor_glosa_enf', NEW.`valor_glosa_enf`, 'valor_glosa_med', NEW.`valor_glosa_med`, 'valor_glosa_total', NEW.`valor_glosa_total`, 'valor_honorarios', NEW.`valor_honorarios`, 'valor_matmed', NEW.`valor_matmed`, 'valor_medicamentos', NEW.`valor_medicamentos`, 'valor_materiais', NEW.`valor_materiais`, 'glosa_materiais', NEW.`glosa_materiais`, 'glosa_medicamentos', NEW.`glosa_medicamentos`, 'valor_oxig', NEW.`valor_oxig`, 'valor_sadt', NEW.`valor_sadt`, 'valor_taxa', NEW.`valor_taxa`, 'valor_opme', NEW.`valor_opme`, 'fk_user_cap', NEW.`fk_user_cap`, 'aberto_cap', NEW.`aberto_cap`, 'encerrado_cap', NEW.`encerrado_cap`, 'glosa_total', NEW.`glosa_total`, 'desconto_valor_cap', NEW.`desconto_valor_cap`, 'negociado_desconto_cap', NEW.`negociado_desconto_cap`, 'protocolo_cap', NEW.`protocolo_cap`, 'em_auditoria_cap', NEW.`em_auditoria_cap`, 'senha_finalizada', NEW.`senha_finalizada`, 'usuario_create_cap', NEW.`usuario_create_cap`, 'data_create_cap', NEW.`data_create_cap`, 'deletado_cap', NEW.`deletado_cap`, 'conta_parada_cap', NEW.`conta_parada_cap`, 'parada_motivo_cap', NEW.`parada_motivo_cap`, 'impresso_cap', NEW.`impresso_cap`, 'fk_id_aud_enf', NEW.`fk_id_aud_enf`, 'fk_id_aud_med', NEW.`fk_id_aud_med`, 'fk_id_aud_adm', NEW.`fk_id_aud_adm`, 'fk_id_aud_hosp', NEW.`fk_id_aud_hosp`, 'validacao_cap', NEW.`validacao_cap`, 'lote_cap', NEW.`lote_cap`, 'updated_at', NEW.`updated_at`, 'conta_faturada_cap', NEW.`conta_faturada_cap`, 'conta_fatura_cap', NEW.`conta_fatura_cap`, 'acomodacao_cap', NEW.`acomodacao_cap`, 'adm_capeante', NEW.`adm_capeante`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_capeante
AFTER DELETE ON tb_capeante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_capeante', 'DELETE', NOW(), OLD.`id_capeante`, JSON_OBJECT('id_capeante', OLD.`id_capeante`, 'fk_int_capeante', OLD.`fk_int_capeante`, 'adm_check', OLD.`adm_check`, 'aud_adm_capeante', OLD.`aud_adm_capeante`, 'aud_enf_capeante', OLD.`aud_enf_capeante`, 'aud_med_capeante', OLD.`aud_med_capeante`, 'data_fech_capeante', OLD.`data_fech_capeante`, 'data_digit_capeante', OLD.`data_digit_capeante`, 'data_final_capeante', OLD.`data_final_capeante`, 'data_inicial_capeante', OLD.`data_inicial_capeante`, 'diarias_capeante', OLD.`diarias_capeante`, 'enfer_check', OLD.`enfer_check`, 'glosa_diaria', OLD.`glosa_diaria`, 'glosa_honorarios', OLD.`glosa_honorarios`, 'glosa_matmed', OLD.`glosa_matmed`, 'glosa_oxig', OLD.`glosa_oxig`, 'glosa_sadt', OLD.`glosa_sadt`, 'glosa_taxas', OLD.`glosa_taxas`, 'glosa_opme', OLD.`glosa_opme`, 'med_check', OLD.`med_check`, 'pacote', OLD.`pacote`, 'parcial_capeante', OLD.`parcial_capeante`, 'parcial_num', OLD.`parcial_num`, 'valor_apresentado_capeante', OLD.`valor_apresentado_capeante`, 'valor_diarias', OLD.`valor_diarias`, 'valor_final_capeante', OLD.`valor_final_capeante`, 'valor_glosa_enf', OLD.`valor_glosa_enf`, 'valor_glosa_med', OLD.`valor_glosa_med`, 'valor_glosa_total', OLD.`valor_glosa_total`, 'valor_honorarios', OLD.`valor_honorarios`, 'valor_matmed', OLD.`valor_matmed`, 'valor_medicamentos', OLD.`valor_medicamentos`, 'valor_materiais', OLD.`valor_materiais`, 'glosa_materiais', OLD.`glosa_materiais`, 'glosa_medicamentos', OLD.`glosa_medicamentos`, 'valor_oxig', OLD.`valor_oxig`, 'valor_sadt', OLD.`valor_sadt`, 'valor_taxa', OLD.`valor_taxa`, 'valor_opme', OLD.`valor_opme`, 'fk_user_cap', OLD.`fk_user_cap`, 'aberto_cap', OLD.`aberto_cap`, 'encerrado_cap', OLD.`encerrado_cap`, 'glosa_total', OLD.`glosa_total`, 'desconto_valor_cap', OLD.`desconto_valor_cap`, 'negociado_desconto_cap', OLD.`negociado_desconto_cap`, 'protocolo_cap', OLD.`protocolo_cap`, 'em_auditoria_cap', OLD.`em_auditoria_cap`, 'senha_finalizada', OLD.`senha_finalizada`, 'usuario_create_cap', OLD.`usuario_create_cap`, 'data_create_cap', OLD.`data_create_cap`, 'deletado_cap', OLD.`deletado_cap`, 'conta_parada_cap', OLD.`conta_parada_cap`, 'parada_motivo_cap', OLD.`parada_motivo_cap`, 'impresso_cap', OLD.`impresso_cap`, 'fk_id_aud_enf', OLD.`fk_id_aud_enf`, 'fk_id_aud_med', OLD.`fk_id_aud_med`, 'fk_id_aud_adm', OLD.`fk_id_aud_adm`, 'fk_id_aud_hosp', OLD.`fk_id_aud_hosp`, 'validacao_cap', OLD.`validacao_cap`, 'lote_cap', OLD.`lote_cap`, 'updated_at', OLD.`updated_at`, 'conta_faturada_cap', OLD.`conta_faturada_cap`, 'conta_fatura_cap', OLD.`conta_fatura_cap`, 'acomodacao_cap', OLD.`acomodacao_cap`, 'adm_capeante', OLD.`adm_capeante`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores
AFTER INSERT ON tb_cap_valores
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores', 'INSERT', NOW(), NEW.`id_valor`, NULL, JSON_OBJECT('id_valor', NEW.`id_valor`, 'fk_capeante', NEW.`fk_capeante`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores
AFTER UPDATE ON tb_cap_valores
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores', 'UPDATE', NOW(), NEW.`id_valor`, JSON_OBJECT('id_valor', OLD.`id_valor`, 'fk_capeante', OLD.`fk_capeante`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), JSON_OBJECT('id_valor', NEW.`id_valor`, 'fk_capeante', NEW.`fk_capeante`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores
AFTER DELETE ON tb_cap_valores
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores', 'DELETE', NOW(), OLD.`id_valor`, JSON_OBJECT('id_valor', OLD.`id_valor`, 'fk_capeante', OLD.`fk_capeante`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores_ap
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores_ap;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores_ap;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores_ap;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores_ap
AFTER INSERT ON tb_cap_valores_ap
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_ap', 'INSERT', NOW(), NEW.`id_ap`, NULL, JSON_OBJECT('id_ap', NEW.`id_ap`, 'fk_capeante', NEW.`fk_capeante`, 'ap_terapias_qtd', NEW.`ap_terapias_qtd`, 'ap_terapias_cobrado', NEW.`ap_terapias_cobrado`, 'ap_terapias_glosado', NEW.`ap_terapias_glosado`, 'ap_terapias_obs', NEW.`ap_terapias_obs`, 'ap_taxas_qtd', NEW.`ap_taxas_qtd`, 'ap_taxas_cobrado', NEW.`ap_taxas_cobrado`, 'ap_taxas_glosado', NEW.`ap_taxas_glosado`, 'ap_taxas_obs', NEW.`ap_taxas_obs`, 'ap_mat_consumo_qtd', NEW.`ap_mat_consumo_qtd`, 'ap_mat_consumo_cobrado', NEW.`ap_mat_consumo_cobrado`, 'ap_mat_consumo_glosado', NEW.`ap_mat_consumo_glosado`, 'ap_mat_consumo_obs', NEW.`ap_mat_consumo_obs`, 'ap_medicametos_qtd', NEW.`ap_medicametos_qtd`, 'ap_medicametos_cobrado', NEW.`ap_medicametos_cobrado`, 'ap_medicametos_glosado', NEW.`ap_medicametos_glosado`, 'ap_medicametos_obs', NEW.`ap_medicametos_obs`, 'ap_gases_qtd', NEW.`ap_gases_qtd`, 'ap_gases_cobrado', NEW.`ap_gases_cobrado`, 'ap_gases_glosado', NEW.`ap_gases_glosado`, 'ap_gases_obs', NEW.`ap_gases_obs`, 'ap_mat_espec_qtd', NEW.`ap_mat_espec_qtd`, 'ap_mat_espec_cobrado', NEW.`ap_mat_espec_cobrado`, 'ap_mat_espec_glosado', NEW.`ap_mat_espec_glosado`, 'ap_mat_espec_obs', NEW.`ap_mat_espec_obs`, 'ap_exames_qtd', NEW.`ap_exames_qtd`, 'ap_exames_cobrado', NEW.`ap_exames_cobrado`, 'ap_exames_glosado', NEW.`ap_exames_glosado`, 'ap_exames_obs', NEW.`ap_exames_obs`, 'ap_hemoderivados_qtd', NEW.`ap_hemoderivados_qtd`, 'ap_hemoderivados_cobrado', NEW.`ap_hemoderivados_cobrado`, 'ap_hemoderivados_glosado', NEW.`ap_hemoderivados_glosado`, 'ap_hemoderivados_obs', NEW.`ap_hemoderivados_obs`, 'ap_honorarios_qtd', NEW.`ap_honorarios_qtd`, 'ap_honorarios_cobrado', NEW.`ap_honorarios_cobrado`, 'ap_honorarios_glosado', NEW.`ap_honorarios_glosado`, 'ap_honorarios_obs', NEW.`ap_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores_ap
AFTER UPDATE ON tb_cap_valores_ap
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_ap', 'UPDATE', NOW(), NEW.`id_ap`, JSON_OBJECT('id_ap', OLD.`id_ap`, 'fk_capeante', OLD.`fk_capeante`, 'ap_terapias_qtd', OLD.`ap_terapias_qtd`, 'ap_terapias_cobrado', OLD.`ap_terapias_cobrado`, 'ap_terapias_glosado', OLD.`ap_terapias_glosado`, 'ap_terapias_obs', OLD.`ap_terapias_obs`, 'ap_taxas_qtd', OLD.`ap_taxas_qtd`, 'ap_taxas_cobrado', OLD.`ap_taxas_cobrado`, 'ap_taxas_glosado', OLD.`ap_taxas_glosado`, 'ap_taxas_obs', OLD.`ap_taxas_obs`, 'ap_mat_consumo_qtd', OLD.`ap_mat_consumo_qtd`, 'ap_mat_consumo_cobrado', OLD.`ap_mat_consumo_cobrado`, 'ap_mat_consumo_glosado', OLD.`ap_mat_consumo_glosado`, 'ap_mat_consumo_obs', OLD.`ap_mat_consumo_obs`, 'ap_medicametos_qtd', OLD.`ap_medicametos_qtd`, 'ap_medicametos_cobrado', OLD.`ap_medicametos_cobrado`, 'ap_medicametos_glosado', OLD.`ap_medicametos_glosado`, 'ap_medicametos_obs', OLD.`ap_medicametos_obs`, 'ap_gases_qtd', OLD.`ap_gases_qtd`, 'ap_gases_cobrado', OLD.`ap_gases_cobrado`, 'ap_gases_glosado', OLD.`ap_gases_glosado`, 'ap_gases_obs', OLD.`ap_gases_obs`, 'ap_mat_espec_qtd', OLD.`ap_mat_espec_qtd`, 'ap_mat_espec_cobrado', OLD.`ap_mat_espec_cobrado`, 'ap_mat_espec_glosado', OLD.`ap_mat_espec_glosado`, 'ap_mat_espec_obs', OLD.`ap_mat_espec_obs`, 'ap_exames_qtd', OLD.`ap_exames_qtd`, 'ap_exames_cobrado', OLD.`ap_exames_cobrado`, 'ap_exames_glosado', OLD.`ap_exames_glosado`, 'ap_exames_obs', OLD.`ap_exames_obs`, 'ap_hemoderivados_qtd', OLD.`ap_hemoderivados_qtd`, 'ap_hemoderivados_cobrado', OLD.`ap_hemoderivados_cobrado`, 'ap_hemoderivados_glosado', OLD.`ap_hemoderivados_glosado`, 'ap_hemoderivados_obs', OLD.`ap_hemoderivados_obs`, 'ap_honorarios_qtd', OLD.`ap_honorarios_qtd`, 'ap_honorarios_cobrado', OLD.`ap_honorarios_cobrado`, 'ap_honorarios_glosado', OLD.`ap_honorarios_glosado`, 'ap_honorarios_obs', OLD.`ap_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), JSON_OBJECT('id_ap', NEW.`id_ap`, 'fk_capeante', NEW.`fk_capeante`, 'ap_terapias_qtd', NEW.`ap_terapias_qtd`, 'ap_terapias_cobrado', NEW.`ap_terapias_cobrado`, 'ap_terapias_glosado', NEW.`ap_terapias_glosado`, 'ap_terapias_obs', NEW.`ap_terapias_obs`, 'ap_taxas_qtd', NEW.`ap_taxas_qtd`, 'ap_taxas_cobrado', NEW.`ap_taxas_cobrado`, 'ap_taxas_glosado', NEW.`ap_taxas_glosado`, 'ap_taxas_obs', NEW.`ap_taxas_obs`, 'ap_mat_consumo_qtd', NEW.`ap_mat_consumo_qtd`, 'ap_mat_consumo_cobrado', NEW.`ap_mat_consumo_cobrado`, 'ap_mat_consumo_glosado', NEW.`ap_mat_consumo_glosado`, 'ap_mat_consumo_obs', NEW.`ap_mat_consumo_obs`, 'ap_medicametos_qtd', NEW.`ap_medicametos_qtd`, 'ap_medicametos_cobrado', NEW.`ap_medicametos_cobrado`, 'ap_medicametos_glosado', NEW.`ap_medicametos_glosado`, 'ap_medicametos_obs', NEW.`ap_medicametos_obs`, 'ap_gases_qtd', NEW.`ap_gases_qtd`, 'ap_gases_cobrado', NEW.`ap_gases_cobrado`, 'ap_gases_glosado', NEW.`ap_gases_glosado`, 'ap_gases_obs', NEW.`ap_gases_obs`, 'ap_mat_espec_qtd', NEW.`ap_mat_espec_qtd`, 'ap_mat_espec_cobrado', NEW.`ap_mat_espec_cobrado`, 'ap_mat_espec_glosado', NEW.`ap_mat_espec_glosado`, 'ap_mat_espec_obs', NEW.`ap_mat_espec_obs`, 'ap_exames_qtd', NEW.`ap_exames_qtd`, 'ap_exames_cobrado', NEW.`ap_exames_cobrado`, 'ap_exames_glosado', NEW.`ap_exames_glosado`, 'ap_exames_obs', NEW.`ap_exames_obs`, 'ap_hemoderivados_qtd', NEW.`ap_hemoderivados_qtd`, 'ap_hemoderivados_cobrado', NEW.`ap_hemoderivados_cobrado`, 'ap_hemoderivados_glosado', NEW.`ap_hemoderivados_glosado`, 'ap_hemoderivados_obs', NEW.`ap_hemoderivados_obs`, 'ap_honorarios_qtd', NEW.`ap_honorarios_qtd`, 'ap_honorarios_cobrado', NEW.`ap_honorarios_cobrado`, 'ap_honorarios_glosado', NEW.`ap_honorarios_glosado`, 'ap_honorarios_obs', NEW.`ap_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores_ap
AFTER DELETE ON tb_cap_valores_ap
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_ap', 'DELETE', NOW(), OLD.`id_ap`, JSON_OBJECT('id_ap', OLD.`id_ap`, 'fk_capeante', OLD.`fk_capeante`, 'ap_terapias_qtd', OLD.`ap_terapias_qtd`, 'ap_terapias_cobrado', OLD.`ap_terapias_cobrado`, 'ap_terapias_glosado', OLD.`ap_terapias_glosado`, 'ap_terapias_obs', OLD.`ap_terapias_obs`, 'ap_taxas_qtd', OLD.`ap_taxas_qtd`, 'ap_taxas_cobrado', OLD.`ap_taxas_cobrado`, 'ap_taxas_glosado', OLD.`ap_taxas_glosado`, 'ap_taxas_obs', OLD.`ap_taxas_obs`, 'ap_mat_consumo_qtd', OLD.`ap_mat_consumo_qtd`, 'ap_mat_consumo_cobrado', OLD.`ap_mat_consumo_cobrado`, 'ap_mat_consumo_glosado', OLD.`ap_mat_consumo_glosado`, 'ap_mat_consumo_obs', OLD.`ap_mat_consumo_obs`, 'ap_medicametos_qtd', OLD.`ap_medicametos_qtd`, 'ap_medicametos_cobrado', OLD.`ap_medicametos_cobrado`, 'ap_medicametos_glosado', OLD.`ap_medicametos_glosado`, 'ap_medicametos_obs', OLD.`ap_medicametos_obs`, 'ap_gases_qtd', OLD.`ap_gases_qtd`, 'ap_gases_cobrado', OLD.`ap_gases_cobrado`, 'ap_gases_glosado', OLD.`ap_gases_glosado`, 'ap_gases_obs', OLD.`ap_gases_obs`, 'ap_mat_espec_qtd', OLD.`ap_mat_espec_qtd`, 'ap_mat_espec_cobrado', OLD.`ap_mat_espec_cobrado`, 'ap_mat_espec_glosado', OLD.`ap_mat_espec_glosado`, 'ap_mat_espec_obs', OLD.`ap_mat_espec_obs`, 'ap_exames_qtd', OLD.`ap_exames_qtd`, 'ap_exames_cobrado', OLD.`ap_exames_cobrado`, 'ap_exames_glosado', OLD.`ap_exames_glosado`, 'ap_exames_obs', OLD.`ap_exames_obs`, 'ap_hemoderivados_qtd', OLD.`ap_hemoderivados_qtd`, 'ap_hemoderivados_cobrado', OLD.`ap_hemoderivados_cobrado`, 'ap_hemoderivados_glosado', OLD.`ap_hemoderivados_glosado`, 'ap_hemoderivados_obs', OLD.`ap_hemoderivados_obs`, 'ap_honorarios_qtd', OLD.`ap_honorarios_qtd`, 'ap_honorarios_cobrado', OLD.`ap_honorarios_cobrado`, 'ap_honorarios_glosado', OLD.`ap_honorarios_glosado`, 'ap_honorarios_obs', OLD.`ap_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores_cc
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores_cc;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores_cc;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores_cc;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores_cc
AFTER INSERT ON tb_cap_valores_cc
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_cc', 'INSERT', NOW(), NEW.`id_cc`, NULL, JSON_OBJECT('id_cc', NEW.`id_cc`, 'fk_capeante', NEW.`fk_capeante`, 'cc_terapias_qtd', NEW.`cc_terapias_qtd`, 'cc_terapias_cobrado', NEW.`cc_terapias_cobrado`, 'cc_terapias_glosado', NEW.`cc_terapias_glosado`, 'cc_terapias_obs', NEW.`cc_terapias_obs`, 'cc_taxas_qtd', NEW.`cc_taxas_qtd`, 'cc_taxas_cobrado', NEW.`cc_taxas_cobrado`, 'cc_taxas_glosado', NEW.`cc_taxas_glosado`, 'cc_taxas_obs', NEW.`cc_taxas_obs`, 'cc_mat_consumo_qtd', NEW.`cc_mat_consumo_qtd`, 'cc_mat_consumo_cobrado', NEW.`cc_mat_consumo_cobrado`, 'cc_mat_consumo_glosado', NEW.`cc_mat_consumo_glosado`, 'cc_mat_consumo_obs', NEW.`cc_mat_consumo_obs`, 'cc_medicametos_qtd', NEW.`cc_medicametos_qtd`, 'cc_medicametos_cobrado', NEW.`cc_medicametos_cobrado`, 'cc_medicametos_glosado', NEW.`cc_medicametos_glosado`, 'cc_medicametos_obs', NEW.`cc_medicametos_obs`, 'cc_gases_qtd', NEW.`cc_gases_qtd`, 'cc_gases_cobrado', NEW.`cc_gases_cobrado`, 'cc_gases_glosado', NEW.`cc_gases_glosado`, 'cc_gases_obs', NEW.`cc_gases_obs`, 'cc_mat_espec_qtd', NEW.`cc_mat_espec_qtd`, 'cc_mat_espec_cobrado', NEW.`cc_mat_espec_cobrado`, 'cc_mat_espec_glosado', NEW.`cc_mat_espec_glosado`, 'cc_mat_espec_obs', NEW.`cc_mat_espec_obs`, 'cc_exames_qtd', NEW.`cc_exames_qtd`, 'cc_exames_cobrado', NEW.`cc_exames_cobrado`, 'cc_exames_glosado', NEW.`cc_exames_glosado`, 'cc_exames_obs', NEW.`cc_exames_obs`, 'cc_hemoderivados_qtd', NEW.`cc_hemoderivados_qtd`, 'cc_hemoderivados_cobrado', NEW.`cc_hemoderivados_cobrado`, 'cc_hemoderivados_glosado', NEW.`cc_hemoderivados_glosado`, 'cc_hemoderivados_obs', NEW.`cc_hemoderivados_obs`, 'cc_honorarios_qtd', NEW.`cc_honorarios_qtd`, 'cc_honorarios_cobrado', NEW.`cc_honorarios_cobrado`, 'cc_honorarios_glosado', NEW.`cc_honorarios_glosado`, 'cc_honorarios_obs', NEW.`cc_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores_cc
AFTER UPDATE ON tb_cap_valores_cc
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_cc', 'UPDATE', NOW(), NEW.`id_cc`, JSON_OBJECT('id_cc', OLD.`id_cc`, 'fk_capeante', OLD.`fk_capeante`, 'cc_terapias_qtd', OLD.`cc_terapias_qtd`, 'cc_terapias_cobrado', OLD.`cc_terapias_cobrado`, 'cc_terapias_glosado', OLD.`cc_terapias_glosado`, 'cc_terapias_obs', OLD.`cc_terapias_obs`, 'cc_taxas_qtd', OLD.`cc_taxas_qtd`, 'cc_taxas_cobrado', OLD.`cc_taxas_cobrado`, 'cc_taxas_glosado', OLD.`cc_taxas_glosado`, 'cc_taxas_obs', OLD.`cc_taxas_obs`, 'cc_mat_consumo_qtd', OLD.`cc_mat_consumo_qtd`, 'cc_mat_consumo_cobrado', OLD.`cc_mat_consumo_cobrado`, 'cc_mat_consumo_glosado', OLD.`cc_mat_consumo_glosado`, 'cc_mat_consumo_obs', OLD.`cc_mat_consumo_obs`, 'cc_medicametos_qtd', OLD.`cc_medicametos_qtd`, 'cc_medicametos_cobrado', OLD.`cc_medicametos_cobrado`, 'cc_medicametos_glosado', OLD.`cc_medicametos_glosado`, 'cc_medicametos_obs', OLD.`cc_medicametos_obs`, 'cc_gases_qtd', OLD.`cc_gases_qtd`, 'cc_gases_cobrado', OLD.`cc_gases_cobrado`, 'cc_gases_glosado', OLD.`cc_gases_glosado`, 'cc_gases_obs', OLD.`cc_gases_obs`, 'cc_mat_espec_qtd', OLD.`cc_mat_espec_qtd`, 'cc_mat_espec_cobrado', OLD.`cc_mat_espec_cobrado`, 'cc_mat_espec_glosado', OLD.`cc_mat_espec_glosado`, 'cc_mat_espec_obs', OLD.`cc_mat_espec_obs`, 'cc_exames_qtd', OLD.`cc_exames_qtd`, 'cc_exames_cobrado', OLD.`cc_exames_cobrado`, 'cc_exames_glosado', OLD.`cc_exames_glosado`, 'cc_exames_obs', OLD.`cc_exames_obs`, 'cc_hemoderivados_qtd', OLD.`cc_hemoderivados_qtd`, 'cc_hemoderivados_cobrado', OLD.`cc_hemoderivados_cobrado`, 'cc_hemoderivados_glosado', OLD.`cc_hemoderivados_glosado`, 'cc_hemoderivados_obs', OLD.`cc_hemoderivados_obs`, 'cc_honorarios_qtd', OLD.`cc_honorarios_qtd`, 'cc_honorarios_cobrado', OLD.`cc_honorarios_cobrado`, 'cc_honorarios_glosado', OLD.`cc_honorarios_glosado`, 'cc_honorarios_obs', OLD.`cc_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), JSON_OBJECT('id_cc', NEW.`id_cc`, 'fk_capeante', NEW.`fk_capeante`, 'cc_terapias_qtd', NEW.`cc_terapias_qtd`, 'cc_terapias_cobrado', NEW.`cc_terapias_cobrado`, 'cc_terapias_glosado', NEW.`cc_terapias_glosado`, 'cc_terapias_obs', NEW.`cc_terapias_obs`, 'cc_taxas_qtd', NEW.`cc_taxas_qtd`, 'cc_taxas_cobrado', NEW.`cc_taxas_cobrado`, 'cc_taxas_glosado', NEW.`cc_taxas_glosado`, 'cc_taxas_obs', NEW.`cc_taxas_obs`, 'cc_mat_consumo_qtd', NEW.`cc_mat_consumo_qtd`, 'cc_mat_consumo_cobrado', NEW.`cc_mat_consumo_cobrado`, 'cc_mat_consumo_glosado', NEW.`cc_mat_consumo_glosado`, 'cc_mat_consumo_obs', NEW.`cc_mat_consumo_obs`, 'cc_medicametos_qtd', NEW.`cc_medicametos_qtd`, 'cc_medicametos_cobrado', NEW.`cc_medicametos_cobrado`, 'cc_medicametos_glosado', NEW.`cc_medicametos_glosado`, 'cc_medicametos_obs', NEW.`cc_medicametos_obs`, 'cc_gases_qtd', NEW.`cc_gases_qtd`, 'cc_gases_cobrado', NEW.`cc_gases_cobrado`, 'cc_gases_glosado', NEW.`cc_gases_glosado`, 'cc_gases_obs', NEW.`cc_gases_obs`, 'cc_mat_espec_qtd', NEW.`cc_mat_espec_qtd`, 'cc_mat_espec_cobrado', NEW.`cc_mat_espec_cobrado`, 'cc_mat_espec_glosado', NEW.`cc_mat_espec_glosado`, 'cc_mat_espec_obs', NEW.`cc_mat_espec_obs`, 'cc_exames_qtd', NEW.`cc_exames_qtd`, 'cc_exames_cobrado', NEW.`cc_exames_cobrado`, 'cc_exames_glosado', NEW.`cc_exames_glosado`, 'cc_exames_obs', NEW.`cc_exames_obs`, 'cc_hemoderivados_qtd', NEW.`cc_hemoderivados_qtd`, 'cc_hemoderivados_cobrado', NEW.`cc_hemoderivados_cobrado`, 'cc_hemoderivados_glosado', NEW.`cc_hemoderivados_glosado`, 'cc_hemoderivados_obs', NEW.`cc_hemoderivados_obs`, 'cc_honorarios_qtd', NEW.`cc_honorarios_qtd`, 'cc_honorarios_cobrado', NEW.`cc_honorarios_cobrado`, 'cc_honorarios_glosado', NEW.`cc_honorarios_glosado`, 'cc_honorarios_obs', NEW.`cc_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores_cc
AFTER DELETE ON tb_cap_valores_cc
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_cc', 'DELETE', NOW(), OLD.`id_cc`, JSON_OBJECT('id_cc', OLD.`id_cc`, 'fk_capeante', OLD.`fk_capeante`, 'cc_terapias_qtd', OLD.`cc_terapias_qtd`, 'cc_terapias_cobrado', OLD.`cc_terapias_cobrado`, 'cc_terapias_glosado', OLD.`cc_terapias_glosado`, 'cc_terapias_obs', OLD.`cc_terapias_obs`, 'cc_taxas_qtd', OLD.`cc_taxas_qtd`, 'cc_taxas_cobrado', OLD.`cc_taxas_cobrado`, 'cc_taxas_glosado', OLD.`cc_taxas_glosado`, 'cc_taxas_obs', OLD.`cc_taxas_obs`, 'cc_mat_consumo_qtd', OLD.`cc_mat_consumo_qtd`, 'cc_mat_consumo_cobrado', OLD.`cc_mat_consumo_cobrado`, 'cc_mat_consumo_glosado', OLD.`cc_mat_consumo_glosado`, 'cc_mat_consumo_obs', OLD.`cc_mat_consumo_obs`, 'cc_medicametos_qtd', OLD.`cc_medicametos_qtd`, 'cc_medicametos_cobrado', OLD.`cc_medicametos_cobrado`, 'cc_medicametos_glosado', OLD.`cc_medicametos_glosado`, 'cc_medicametos_obs', OLD.`cc_medicametos_obs`, 'cc_gases_qtd', OLD.`cc_gases_qtd`, 'cc_gases_cobrado', OLD.`cc_gases_cobrado`, 'cc_gases_glosado', OLD.`cc_gases_glosado`, 'cc_gases_obs', OLD.`cc_gases_obs`, 'cc_mat_espec_qtd', OLD.`cc_mat_espec_qtd`, 'cc_mat_espec_cobrado', OLD.`cc_mat_espec_cobrado`, 'cc_mat_espec_glosado', OLD.`cc_mat_espec_glosado`, 'cc_mat_espec_obs', OLD.`cc_mat_espec_obs`, 'cc_exames_qtd', OLD.`cc_exames_qtd`, 'cc_exames_cobrado', OLD.`cc_exames_cobrado`, 'cc_exames_glosado', OLD.`cc_exames_glosado`, 'cc_exames_obs', OLD.`cc_exames_obs`, 'cc_hemoderivados_qtd', OLD.`cc_hemoderivados_qtd`, 'cc_hemoderivados_cobrado', OLD.`cc_hemoderivados_cobrado`, 'cc_hemoderivados_glosado', OLD.`cc_hemoderivados_glosado`, 'cc_hemoderivados_obs', OLD.`cc_hemoderivados_obs`, 'cc_honorarios_qtd', OLD.`cc_honorarios_qtd`, 'cc_honorarios_cobrado', OLD.`cc_honorarios_cobrado`, 'cc_honorarios_glosado', OLD.`cc_honorarios_glosado`, 'cc_honorarios_obs', OLD.`cc_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores_diar
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores_diar;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores_diar;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores_diar;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores_diar
AFTER INSERT ON tb_cap_valores_diar
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_diar', 'INSERT', NOW(), NEW.`id_diar`, NULL, JSON_OBJECT('id_diar', NEW.`id_diar`, 'fk_capeante', NEW.`fk_capeante`, 'ac_quarto_qtd', NEW.`ac_quarto_qtd`, 'ac_quarto_cobrado', NEW.`ac_quarto_cobrado`, 'ac_quarto_glosado', NEW.`ac_quarto_glosado`, 'ac_quarto_obs', NEW.`ac_quarto_obs`, 'ac_dayclinic_qtd', NEW.`ac_dayclinic_qtd`, 'ac_dayclinic_cobrado', NEW.`ac_dayclinic_cobrado`, 'ac_dayclinic_glosado', NEW.`ac_dayclinic_glosado`, 'ac_dayclinic_obs', NEW.`ac_dayclinic_obs`, 'ac_uti_qtd', NEW.`ac_uti_qtd`, 'ac_uti_cobrado', NEW.`ac_uti_cobrado`, 'ac_uti_glosado', NEW.`ac_uti_glosado`, 'ac_uti_obs', NEW.`ac_uti_obs`, 'ac_utisemi_qtd', NEW.`ac_utisemi_qtd`, 'ac_utisemi_cobrado', NEW.`ac_utisemi_cobrado`, 'ac_utisemi_glosado', NEW.`ac_utisemi_glosado`, 'ac_utisemi_obs', NEW.`ac_utisemi_obs`, 'ac_enfermaria_qtd', NEW.`ac_enfermaria_qtd`, 'ac_enfermaria_cobrado', NEW.`ac_enfermaria_cobrado`, 'ac_enfermaria_glosado', NEW.`ac_enfermaria_glosado`, 'ac_enfermaria_obs', NEW.`ac_enfermaria_obs`, 'ac_bercario_qtd', NEW.`ac_bercario_qtd`, 'ac_bercario_cobrado', NEW.`ac_bercario_cobrado`, 'ac_bercario_glosado', NEW.`ac_bercario_glosado`, 'ac_bercario_obs', NEW.`ac_bercario_obs`, 'ac_acompanhante_qtd', NEW.`ac_acompanhante_qtd`, 'ac_acompanhante_cobrado', NEW.`ac_acompanhante_cobrado`, 'ac_acompanhante_glosado', NEW.`ac_acompanhante_glosado`, 'ac_acompanhante_obs', NEW.`ac_acompanhante_obs`, 'ac_isolamento_qtd', NEW.`ac_isolamento_qtd`, 'ac_isolamento_cobrado', NEW.`ac_isolamento_cobrado`, 'ac_isolamento_glosado', NEW.`ac_isolamento_glosado`, 'ac_isolamento_obs', NEW.`ac_isolamento_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores_diar
AFTER UPDATE ON tb_cap_valores_diar
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_diar', 'UPDATE', NOW(), NEW.`id_diar`, JSON_OBJECT('id_diar', OLD.`id_diar`, 'fk_capeante', OLD.`fk_capeante`, 'ac_quarto_qtd', OLD.`ac_quarto_qtd`, 'ac_quarto_cobrado', OLD.`ac_quarto_cobrado`, 'ac_quarto_glosado', OLD.`ac_quarto_glosado`, 'ac_quarto_obs', OLD.`ac_quarto_obs`, 'ac_dayclinic_qtd', OLD.`ac_dayclinic_qtd`, 'ac_dayclinic_cobrado', OLD.`ac_dayclinic_cobrado`, 'ac_dayclinic_glosado', OLD.`ac_dayclinic_glosado`, 'ac_dayclinic_obs', OLD.`ac_dayclinic_obs`, 'ac_uti_qtd', OLD.`ac_uti_qtd`, 'ac_uti_cobrado', OLD.`ac_uti_cobrado`, 'ac_uti_glosado', OLD.`ac_uti_glosado`, 'ac_uti_obs', OLD.`ac_uti_obs`, 'ac_utisemi_qtd', OLD.`ac_utisemi_qtd`, 'ac_utisemi_cobrado', OLD.`ac_utisemi_cobrado`, 'ac_utisemi_glosado', OLD.`ac_utisemi_glosado`, 'ac_utisemi_obs', OLD.`ac_utisemi_obs`, 'ac_enfermaria_qtd', OLD.`ac_enfermaria_qtd`, 'ac_enfermaria_cobrado', OLD.`ac_enfermaria_cobrado`, 'ac_enfermaria_glosado', OLD.`ac_enfermaria_glosado`, 'ac_enfermaria_obs', OLD.`ac_enfermaria_obs`, 'ac_bercario_qtd', OLD.`ac_bercario_qtd`, 'ac_bercario_cobrado', OLD.`ac_bercario_cobrado`, 'ac_bercario_glosado', OLD.`ac_bercario_glosado`, 'ac_bercario_obs', OLD.`ac_bercario_obs`, 'ac_acompanhante_qtd', OLD.`ac_acompanhante_qtd`, 'ac_acompanhante_cobrado', OLD.`ac_acompanhante_cobrado`, 'ac_acompanhante_glosado', OLD.`ac_acompanhante_glosado`, 'ac_acompanhante_obs', OLD.`ac_acompanhante_obs`, 'ac_isolamento_qtd', OLD.`ac_isolamento_qtd`, 'ac_isolamento_cobrado', OLD.`ac_isolamento_cobrado`, 'ac_isolamento_glosado', OLD.`ac_isolamento_glosado`, 'ac_isolamento_obs', OLD.`ac_isolamento_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), JSON_OBJECT('id_diar', NEW.`id_diar`, 'fk_capeante', NEW.`fk_capeante`, 'ac_quarto_qtd', NEW.`ac_quarto_qtd`, 'ac_quarto_cobrado', NEW.`ac_quarto_cobrado`, 'ac_quarto_glosado', NEW.`ac_quarto_glosado`, 'ac_quarto_obs', NEW.`ac_quarto_obs`, 'ac_dayclinic_qtd', NEW.`ac_dayclinic_qtd`, 'ac_dayclinic_cobrado', NEW.`ac_dayclinic_cobrado`, 'ac_dayclinic_glosado', NEW.`ac_dayclinic_glosado`, 'ac_dayclinic_obs', NEW.`ac_dayclinic_obs`, 'ac_uti_qtd', NEW.`ac_uti_qtd`, 'ac_uti_cobrado', NEW.`ac_uti_cobrado`, 'ac_uti_glosado', NEW.`ac_uti_glosado`, 'ac_uti_obs', NEW.`ac_uti_obs`, 'ac_utisemi_qtd', NEW.`ac_utisemi_qtd`, 'ac_utisemi_cobrado', NEW.`ac_utisemi_cobrado`, 'ac_utisemi_glosado', NEW.`ac_utisemi_glosado`, 'ac_utisemi_obs', NEW.`ac_utisemi_obs`, 'ac_enfermaria_qtd', NEW.`ac_enfermaria_qtd`, 'ac_enfermaria_cobrado', NEW.`ac_enfermaria_cobrado`, 'ac_enfermaria_glosado', NEW.`ac_enfermaria_glosado`, 'ac_enfermaria_obs', NEW.`ac_enfermaria_obs`, 'ac_bercario_qtd', NEW.`ac_bercario_qtd`, 'ac_bercario_cobrado', NEW.`ac_bercario_cobrado`, 'ac_bercario_glosado', NEW.`ac_bercario_glosado`, 'ac_bercario_obs', NEW.`ac_bercario_obs`, 'ac_acompanhante_qtd', NEW.`ac_acompanhante_qtd`, 'ac_acompanhante_cobrado', NEW.`ac_acompanhante_cobrado`, 'ac_acompanhante_glosado', NEW.`ac_acompanhante_glosado`, 'ac_acompanhante_obs', NEW.`ac_acompanhante_obs`, 'ac_isolamento_qtd', NEW.`ac_isolamento_qtd`, 'ac_isolamento_cobrado', NEW.`ac_isolamento_cobrado`, 'ac_isolamento_glosado', NEW.`ac_isolamento_glosado`, 'ac_isolamento_obs', NEW.`ac_isolamento_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores_diar
AFTER DELETE ON tb_cap_valores_diar
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_diar', 'DELETE', NOW(), OLD.`id_diar`, JSON_OBJECT('id_diar', OLD.`id_diar`, 'fk_capeante', OLD.`fk_capeante`, 'ac_quarto_qtd', OLD.`ac_quarto_qtd`, 'ac_quarto_cobrado', OLD.`ac_quarto_cobrado`, 'ac_quarto_glosado', OLD.`ac_quarto_glosado`, 'ac_quarto_obs', OLD.`ac_quarto_obs`, 'ac_dayclinic_qtd', OLD.`ac_dayclinic_qtd`, 'ac_dayclinic_cobrado', OLD.`ac_dayclinic_cobrado`, 'ac_dayclinic_glosado', OLD.`ac_dayclinic_glosado`, 'ac_dayclinic_obs', OLD.`ac_dayclinic_obs`, 'ac_uti_qtd', OLD.`ac_uti_qtd`, 'ac_uti_cobrado', OLD.`ac_uti_cobrado`, 'ac_uti_glosado', OLD.`ac_uti_glosado`, 'ac_uti_obs', OLD.`ac_uti_obs`, 'ac_utisemi_qtd', OLD.`ac_utisemi_qtd`, 'ac_utisemi_cobrado', OLD.`ac_utisemi_cobrado`, 'ac_utisemi_glosado', OLD.`ac_utisemi_glosado`, 'ac_utisemi_obs', OLD.`ac_utisemi_obs`, 'ac_enfermaria_qtd', OLD.`ac_enfermaria_qtd`, 'ac_enfermaria_cobrado', OLD.`ac_enfermaria_cobrado`, 'ac_enfermaria_glosado', OLD.`ac_enfermaria_glosado`, 'ac_enfermaria_obs', OLD.`ac_enfermaria_obs`, 'ac_bercario_qtd', OLD.`ac_bercario_qtd`, 'ac_bercario_cobrado', OLD.`ac_bercario_cobrado`, 'ac_bercario_glosado', OLD.`ac_bercario_glosado`, 'ac_bercario_obs', OLD.`ac_bercario_obs`, 'ac_acompanhante_qtd', OLD.`ac_acompanhante_qtd`, 'ac_acompanhante_cobrado', OLD.`ac_acompanhante_cobrado`, 'ac_acompanhante_glosado', OLD.`ac_acompanhante_glosado`, 'ac_acompanhante_obs', OLD.`ac_acompanhante_obs`, 'ac_isolamento_qtd', OLD.`ac_isolamento_qtd`, 'ac_isolamento_cobrado', OLD.`ac_isolamento_cobrado`, 'ac_isolamento_glosado', OLD.`ac_isolamento_glosado`, 'ac_isolamento_obs', OLD.`ac_isolamento_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores_out
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores_out;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores_out;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores_out;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores_out
AFTER INSERT ON tb_cap_valores_out
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_out', 'INSERT', NOW(), NEW.`id_out`, NULL, JSON_OBJECT('id_out', NEW.`id_out`, 'fk_capeante', NEW.`fk_capeante`, 'fk_int_capeante', NEW.`fk_int_capeante`, 'outros_pacote_qtd', NEW.`outros_pacote_qtd`, 'outros_pacote_cobrado', NEW.`outros_pacote_cobrado`, 'outros_pacote_glosado', NEW.`outros_pacote_glosado`, 'outros_pacote_liberado', NEW.`outros_pacote_liberado`, 'outros_pacote_obs', NEW.`outros_pacote_obs`, 'outros_remocao_qtd', NEW.`outros_remocao_qtd`, 'outros_remocao_cobrado', NEW.`outros_remocao_cobrado`, 'outros_remocao_glosado', NEW.`outros_remocao_glosado`, 'outros_remocao_liberado', NEW.`outros_remocao_liberado`, 'outros_remocao_obs', NEW.`outros_remocao_obs`, 'outros_desconto_out', NEW.`outros_desconto_out`, 'comentarios_obs', NEW.`comentarios_obs`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores_out
AFTER UPDATE ON tb_cap_valores_out
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_out', 'UPDATE', NOW(), NEW.`id_out`, JSON_OBJECT('id_out', OLD.`id_out`, 'fk_capeante', OLD.`fk_capeante`, 'fk_int_capeante', OLD.`fk_int_capeante`, 'outros_pacote_qtd', OLD.`outros_pacote_qtd`, 'outros_pacote_cobrado', OLD.`outros_pacote_cobrado`, 'outros_pacote_glosado', OLD.`outros_pacote_glosado`, 'outros_pacote_liberado', OLD.`outros_pacote_liberado`, 'outros_pacote_obs', OLD.`outros_pacote_obs`, 'outros_remocao_qtd', OLD.`outros_remocao_qtd`, 'outros_remocao_cobrado', OLD.`outros_remocao_cobrado`, 'outros_remocao_glosado', OLD.`outros_remocao_glosado`, 'outros_remocao_liberado', OLD.`outros_remocao_liberado`, 'outros_remocao_obs', OLD.`outros_remocao_obs`, 'outros_desconto_out', OLD.`outros_desconto_out`, 'comentarios_obs', OLD.`comentarios_obs`), JSON_OBJECT('id_out', NEW.`id_out`, 'fk_capeante', NEW.`fk_capeante`, 'fk_int_capeante', NEW.`fk_int_capeante`, 'outros_pacote_qtd', NEW.`outros_pacote_qtd`, 'outros_pacote_cobrado', NEW.`outros_pacote_cobrado`, 'outros_pacote_glosado', NEW.`outros_pacote_glosado`, 'outros_pacote_liberado', NEW.`outros_pacote_liberado`, 'outros_pacote_obs', NEW.`outros_pacote_obs`, 'outros_remocao_qtd', NEW.`outros_remocao_qtd`, 'outros_remocao_cobrado', NEW.`outros_remocao_cobrado`, 'outros_remocao_glosado', NEW.`outros_remocao_glosado`, 'outros_remocao_liberado', NEW.`outros_remocao_liberado`, 'outros_remocao_obs', NEW.`outros_remocao_obs`, 'outros_desconto_out', NEW.`outros_desconto_out`, 'comentarios_obs', NEW.`comentarios_obs`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores_out
AFTER DELETE ON tb_cap_valores_out
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_out', 'DELETE', NOW(), OLD.`id_out`, JSON_OBJECT('id_out', OLD.`id_out`, 'fk_capeante', OLD.`fk_capeante`, 'fk_int_capeante', OLD.`fk_int_capeante`, 'outros_pacote_qtd', OLD.`outros_pacote_qtd`, 'outros_pacote_cobrado', OLD.`outros_pacote_cobrado`, 'outros_pacote_glosado', OLD.`outros_pacote_glosado`, 'outros_pacote_liberado', OLD.`outros_pacote_liberado`, 'outros_pacote_obs', OLD.`outros_pacote_obs`, 'outros_remocao_qtd', OLD.`outros_remocao_qtd`, 'outros_remocao_cobrado', OLD.`outros_remocao_cobrado`, 'outros_remocao_glosado', OLD.`outros_remocao_glosado`, 'outros_remocao_liberado', OLD.`outros_remocao_liberado`, 'outros_remocao_obs', OLD.`outros_remocao_obs`, 'outros_desconto_out', OLD.`outros_desconto_out`, 'comentarios_obs', OLD.`comentarios_obs`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cap_valores_uti
DROP TRIGGER IF EXISTS trg_log_insert_tb_cap_valores_uti;
DROP TRIGGER IF EXISTS trg_log_update_tb_cap_valores_uti;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cap_valores_uti;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cap_valores_uti
AFTER INSERT ON tb_cap_valores_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_uti', 'INSERT', NOW(), NEW.`id_uti`, NULL, JSON_OBJECT('id_uti', NEW.`id_uti`, 'fk_capeante', NEW.`fk_capeante`, 'uti_terapias_qtd', NEW.`uti_terapias_qtd`, 'uti_terapias_cobrado', NEW.`uti_terapias_cobrado`, 'uti_terapias_glosado', NEW.`uti_terapias_glosado`, 'uti_terapias_obs', NEW.`uti_terapias_obs`, 'uti_taxas_qtd', NEW.`uti_taxas_qtd`, 'uti_taxas_cobrado', NEW.`uti_taxas_cobrado`, 'uti_taxas_glosado', NEW.`uti_taxas_glosado`, 'uti_taxas_obs', NEW.`uti_taxas_obs`, 'uti_mat_consumo_qtd', NEW.`uti_mat_consumo_qtd`, 'uti_mat_consumo_cobrado', NEW.`uti_mat_consumo_cobrado`, 'uti_mat_consumo_glosado', NEW.`uti_mat_consumo_glosado`, 'uti_mat_consumo_obs', NEW.`uti_mat_consumo_obs`, 'uti_medicametos_qtd', NEW.`uti_medicametos_qtd`, 'uti_medicametos_cobrado', NEW.`uti_medicametos_cobrado`, 'uti_medicametos_glosado', NEW.`uti_medicametos_glosado`, 'uti_medicametos_obs', NEW.`uti_medicametos_obs`, 'uti_gases_qtd', NEW.`uti_gases_qtd`, 'uti_gases_cobrado', NEW.`uti_gases_cobrado`, 'uti_gases_glosado', NEW.`uti_gases_glosado`, 'uti_gases_obs', NEW.`uti_gases_obs`, 'uti_mat_espec_qtd', NEW.`uti_mat_espec_qtd`, 'uti_mat_espec_cobrado', NEW.`uti_mat_espec_cobrado`, 'uti_mat_espec_glosado', NEW.`uti_mat_espec_glosado`, 'uti_mat_espec_obs', NEW.`uti_mat_espec_obs`, 'uti_exames_qtd', NEW.`uti_exames_qtd`, 'uti_exames_cobrado', NEW.`uti_exames_cobrado`, 'uti_exames_glosado', NEW.`uti_exames_glosado`, 'uti_exames_obs', NEW.`uti_exames_obs`, 'uti_hemoderivados_qtd', NEW.`uti_hemoderivados_qtd`, 'uti_hemoderivados_cobrado', NEW.`uti_hemoderivados_cobrado`, 'uti_hemoderivados_glosado', NEW.`uti_hemoderivados_glosado`, 'uti_hemoderivados_obs', NEW.`uti_hemoderivados_obs`, 'uti_honorarios_qtd', NEW.`uti_honorarios_qtd`, 'uti_honorarios_cobrado', NEW.`uti_honorarios_cobrado`, 'uti_honorarios_glosado', NEW.`uti_honorarios_glosado`, 'uti_honorarios_obs', NEW.`uti_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cap_valores_uti
AFTER UPDATE ON tb_cap_valores_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_uti', 'UPDATE', NOW(), NEW.`id_uti`, JSON_OBJECT('id_uti', OLD.`id_uti`, 'fk_capeante', OLD.`fk_capeante`, 'uti_terapias_qtd', OLD.`uti_terapias_qtd`, 'uti_terapias_cobrado', OLD.`uti_terapias_cobrado`, 'uti_terapias_glosado', OLD.`uti_terapias_glosado`, 'uti_terapias_obs', OLD.`uti_terapias_obs`, 'uti_taxas_qtd', OLD.`uti_taxas_qtd`, 'uti_taxas_cobrado', OLD.`uti_taxas_cobrado`, 'uti_taxas_glosado', OLD.`uti_taxas_glosado`, 'uti_taxas_obs', OLD.`uti_taxas_obs`, 'uti_mat_consumo_qtd', OLD.`uti_mat_consumo_qtd`, 'uti_mat_consumo_cobrado', OLD.`uti_mat_consumo_cobrado`, 'uti_mat_consumo_glosado', OLD.`uti_mat_consumo_glosado`, 'uti_mat_consumo_obs', OLD.`uti_mat_consumo_obs`, 'uti_medicametos_qtd', OLD.`uti_medicametos_qtd`, 'uti_medicametos_cobrado', OLD.`uti_medicametos_cobrado`, 'uti_medicametos_glosado', OLD.`uti_medicametos_glosado`, 'uti_medicametos_obs', OLD.`uti_medicametos_obs`, 'uti_gases_qtd', OLD.`uti_gases_qtd`, 'uti_gases_cobrado', OLD.`uti_gases_cobrado`, 'uti_gases_glosado', OLD.`uti_gases_glosado`, 'uti_gases_obs', OLD.`uti_gases_obs`, 'uti_mat_espec_qtd', OLD.`uti_mat_espec_qtd`, 'uti_mat_espec_cobrado', OLD.`uti_mat_espec_cobrado`, 'uti_mat_espec_glosado', OLD.`uti_mat_espec_glosado`, 'uti_mat_espec_obs', OLD.`uti_mat_espec_obs`, 'uti_exames_qtd', OLD.`uti_exames_qtd`, 'uti_exames_cobrado', OLD.`uti_exames_cobrado`, 'uti_exames_glosado', OLD.`uti_exames_glosado`, 'uti_exames_obs', OLD.`uti_exames_obs`, 'uti_hemoderivados_qtd', OLD.`uti_hemoderivados_qtd`, 'uti_hemoderivados_cobrado', OLD.`uti_hemoderivados_cobrado`, 'uti_hemoderivados_glosado', OLD.`uti_hemoderivados_glosado`, 'uti_hemoderivados_obs', OLD.`uti_hemoderivados_obs`, 'uti_honorarios_qtd', OLD.`uti_honorarios_qtd`, 'uti_honorarios_cobrado', OLD.`uti_honorarios_cobrado`, 'uti_honorarios_glosado', OLD.`uti_honorarios_glosado`, 'uti_honorarios_obs', OLD.`uti_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), JSON_OBJECT('id_uti', NEW.`id_uti`, 'fk_capeante', NEW.`fk_capeante`, 'uti_terapias_qtd', NEW.`uti_terapias_qtd`, 'uti_terapias_cobrado', NEW.`uti_terapias_cobrado`, 'uti_terapias_glosado', NEW.`uti_terapias_glosado`, 'uti_terapias_obs', NEW.`uti_terapias_obs`, 'uti_taxas_qtd', NEW.`uti_taxas_qtd`, 'uti_taxas_cobrado', NEW.`uti_taxas_cobrado`, 'uti_taxas_glosado', NEW.`uti_taxas_glosado`, 'uti_taxas_obs', NEW.`uti_taxas_obs`, 'uti_mat_consumo_qtd', NEW.`uti_mat_consumo_qtd`, 'uti_mat_consumo_cobrado', NEW.`uti_mat_consumo_cobrado`, 'uti_mat_consumo_glosado', NEW.`uti_mat_consumo_glosado`, 'uti_mat_consumo_obs', NEW.`uti_mat_consumo_obs`, 'uti_medicametos_qtd', NEW.`uti_medicametos_qtd`, 'uti_medicametos_cobrado', NEW.`uti_medicametos_cobrado`, 'uti_medicametos_glosado', NEW.`uti_medicametos_glosado`, 'uti_medicametos_obs', NEW.`uti_medicametos_obs`, 'uti_gases_qtd', NEW.`uti_gases_qtd`, 'uti_gases_cobrado', NEW.`uti_gases_cobrado`, 'uti_gases_glosado', NEW.`uti_gases_glosado`, 'uti_gases_obs', NEW.`uti_gases_obs`, 'uti_mat_espec_qtd', NEW.`uti_mat_espec_qtd`, 'uti_mat_espec_cobrado', NEW.`uti_mat_espec_cobrado`, 'uti_mat_espec_glosado', NEW.`uti_mat_espec_glosado`, 'uti_mat_espec_obs', NEW.`uti_mat_espec_obs`, 'uti_exames_qtd', NEW.`uti_exames_qtd`, 'uti_exames_cobrado', NEW.`uti_exames_cobrado`, 'uti_exames_glosado', NEW.`uti_exames_glosado`, 'uti_exames_obs', NEW.`uti_exames_obs`, 'uti_hemoderivados_qtd', NEW.`uti_hemoderivados_qtd`, 'uti_hemoderivados_cobrado', NEW.`uti_hemoderivados_cobrado`, 'uti_hemoderivados_glosado', NEW.`uti_hemoderivados_glosado`, 'uti_hemoderivados_obs', NEW.`uti_hemoderivados_obs`, 'uti_honorarios_qtd', NEW.`uti_honorarios_qtd`, 'uti_honorarios_cobrado', NEW.`uti_honorarios_cobrado`, 'uti_honorarios_glosado', NEW.`uti_honorarios_glosado`, 'uti_honorarios_obs', NEW.`uti_honorarios_obs`, 'criado_em', NEW.`criado_em`, 'atualizado_em', NEW.`atualizado_em`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cap_valores_uti
AFTER DELETE ON tb_cap_valores_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cap_valores_uti', 'DELETE', NOW(), OLD.`id_uti`, JSON_OBJECT('id_uti', OLD.`id_uti`, 'fk_capeante', OLD.`fk_capeante`, 'uti_terapias_qtd', OLD.`uti_terapias_qtd`, 'uti_terapias_cobrado', OLD.`uti_terapias_cobrado`, 'uti_terapias_glosado', OLD.`uti_terapias_glosado`, 'uti_terapias_obs', OLD.`uti_terapias_obs`, 'uti_taxas_qtd', OLD.`uti_taxas_qtd`, 'uti_taxas_cobrado', OLD.`uti_taxas_cobrado`, 'uti_taxas_glosado', OLD.`uti_taxas_glosado`, 'uti_taxas_obs', OLD.`uti_taxas_obs`, 'uti_mat_consumo_qtd', OLD.`uti_mat_consumo_qtd`, 'uti_mat_consumo_cobrado', OLD.`uti_mat_consumo_cobrado`, 'uti_mat_consumo_glosado', OLD.`uti_mat_consumo_glosado`, 'uti_mat_consumo_obs', OLD.`uti_mat_consumo_obs`, 'uti_medicametos_qtd', OLD.`uti_medicametos_qtd`, 'uti_medicametos_cobrado', OLD.`uti_medicametos_cobrado`, 'uti_medicametos_glosado', OLD.`uti_medicametos_glosado`, 'uti_medicametos_obs', OLD.`uti_medicametos_obs`, 'uti_gases_qtd', OLD.`uti_gases_qtd`, 'uti_gases_cobrado', OLD.`uti_gases_cobrado`, 'uti_gases_glosado', OLD.`uti_gases_glosado`, 'uti_gases_obs', OLD.`uti_gases_obs`, 'uti_mat_espec_qtd', OLD.`uti_mat_espec_qtd`, 'uti_mat_espec_cobrado', OLD.`uti_mat_espec_cobrado`, 'uti_mat_espec_glosado', OLD.`uti_mat_espec_glosado`, 'uti_mat_espec_obs', OLD.`uti_mat_espec_obs`, 'uti_exames_qtd', OLD.`uti_exames_qtd`, 'uti_exames_cobrado', OLD.`uti_exames_cobrado`, 'uti_exames_glosado', OLD.`uti_exames_glosado`, 'uti_exames_obs', OLD.`uti_exames_obs`, 'uti_hemoderivados_qtd', OLD.`uti_hemoderivados_qtd`, 'uti_hemoderivados_cobrado', OLD.`uti_hemoderivados_cobrado`, 'uti_hemoderivados_glosado', OLD.`uti_hemoderivados_glosado`, 'uti_hemoderivados_obs', OLD.`uti_hemoderivados_obs`, 'uti_honorarios_qtd', OLD.`uti_honorarios_qtd`, 'uti_honorarios_cobrado', OLD.`uti_honorarios_cobrado`, 'uti_honorarios_glosado', OLD.`uti_honorarios_glosado`, 'uti_honorarios_obs', OLD.`uti_honorarios_obs`, 'criado_em', OLD.`criado_em`, 'atualizado_em', OLD.`atualizado_em`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_censo
DROP TRIGGER IF EXISTS trg_log_insert_tb_censo;
DROP TRIGGER IF EXISTS trg_log_update_tb_censo;
DROP TRIGGER IF EXISTS trg_log_delete_tb_censo;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_censo
AFTER INSERT ON tb_censo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_censo', 'INSERT', NOW(), NEW.`id_censo`, NULL, JSON_OBJECT('id_censo', NEW.`id_censo`, 'fk_paciente_censo', NEW.`fk_paciente_censo`, 'fk_hospital_censo', NEW.`fk_hospital_censo`, 'data_censo', NEW.`data_censo`, 'senha_censo', NEW.`senha_censo`, 'acomodacao_censo', NEW.`acomodacao_censo`, 'tipo_admissao_censo', NEW.`tipo_admissao_censo`, 'modo_internacao_censo', NEW.`modo_internacao_censo`, 'usuario_create_censo', NEW.`usuario_create_censo`, 'data_create_censo', NEW.`data_create_censo`, 'titular_censo', NEW.`titular_censo`, 'internado', NEW.`internado`, 'deletado_censo', NEW.`deletado_censo`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_censo
AFTER UPDATE ON tb_censo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_censo', 'UPDATE', NOW(), NEW.`id_censo`, JSON_OBJECT('id_censo', OLD.`id_censo`, 'fk_paciente_censo', OLD.`fk_paciente_censo`, 'fk_hospital_censo', OLD.`fk_hospital_censo`, 'data_censo', OLD.`data_censo`, 'senha_censo', OLD.`senha_censo`, 'acomodacao_censo', OLD.`acomodacao_censo`, 'tipo_admissao_censo', OLD.`tipo_admissao_censo`, 'modo_internacao_censo', OLD.`modo_internacao_censo`, 'usuario_create_censo', OLD.`usuario_create_censo`, 'data_create_censo', OLD.`data_create_censo`, 'titular_censo', OLD.`titular_censo`, 'internado', OLD.`internado`, 'deletado_censo', OLD.`deletado_censo`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_censo', NEW.`id_censo`, 'fk_paciente_censo', NEW.`fk_paciente_censo`, 'fk_hospital_censo', NEW.`fk_hospital_censo`, 'data_censo', NEW.`data_censo`, 'senha_censo', NEW.`senha_censo`, 'acomodacao_censo', NEW.`acomodacao_censo`, 'tipo_admissao_censo', NEW.`tipo_admissao_censo`, 'modo_internacao_censo', NEW.`modo_internacao_censo`, 'usuario_create_censo', NEW.`usuario_create_censo`, 'data_create_censo', NEW.`data_create_censo`, 'titular_censo', NEW.`titular_censo`, 'internado', NEW.`internado`, 'deletado_censo', NEW.`deletado_censo`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_censo
AFTER DELETE ON tb_censo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_censo', 'DELETE', NOW(), OLD.`id_censo`, JSON_OBJECT('id_censo', OLD.`id_censo`, 'fk_paciente_censo', OLD.`fk_paciente_censo`, 'fk_hospital_censo', OLD.`fk_hospital_censo`, 'data_censo', OLD.`data_censo`, 'senha_censo', OLD.`senha_censo`, 'acomodacao_censo', OLD.`acomodacao_censo`, 'tipo_admissao_censo', OLD.`tipo_admissao_censo`, 'modo_internacao_censo', OLD.`modo_internacao_censo`, 'usuario_create_censo', OLD.`usuario_create_censo`, 'data_create_censo', OLD.`data_create_censo`, 'titular_censo', OLD.`titular_censo`, 'internado', OLD.`internado`, 'deletado_censo', OLD.`deletado_censo`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_cid
DROP TRIGGER IF EXISTS trg_log_insert_tb_cid;
DROP TRIGGER IF EXISTS trg_log_update_tb_cid;
DROP TRIGGER IF EXISTS trg_log_delete_tb_cid;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_cid
AFTER INSERT ON tb_cid
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cid', 'INSERT', NOW(), NEW.`id_cid`, NULL, JSON_OBJECT('id_cid', NEW.`id_cid`, 'cat', NEW.`cat`, 'descricao', NEW.`descricao`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_cid
AFTER UPDATE ON tb_cid
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cid', 'UPDATE', NOW(), NEW.`id_cid`, JSON_OBJECT('id_cid', OLD.`id_cid`, 'cat', OLD.`cat`, 'descricao', OLD.`descricao`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_cid', NEW.`id_cid`, 'cat', NEW.`cat`, 'descricao', NEW.`descricao`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_cid
AFTER DELETE ON tb_cid
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_cid', 'DELETE', NOW(), OLD.`id_cid`, JSON_OBJECT('id_cid', OLD.`id_cid`, 'cat', OLD.`cat`, 'descricao', OLD.`descricao`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_detalhes
DROP TRIGGER IF EXISTS trg_log_insert_tb_detalhes;
DROP TRIGGER IF EXISTS trg_log_update_tb_detalhes;
DROP TRIGGER IF EXISTS trg_log_delete_tb_detalhes;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_detalhes
AFTER INSERT ON tb_detalhes
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_detalhes', 'INSERT', NOW(), NEW.`id_detalhes`, NULL, JSON_OBJECT('id_detalhes', NEW.`id_detalhes`, 'fk_int_det', NEW.`fk_int_det`, 'fk_vis_det', NEW.`fk_vis_det`, 'curativo_det', NEW.`curativo_det`, 'dieta_det', NEW.`dieta_det`, 'nivel_consc_det', NEW.`nivel_consc_det`, 'oxig_det', NEW.`oxig_det`, 'oxig_uso_det', NEW.`oxig_uso_det`, 'qt_det', NEW.`qt_det`, 'rt_det', NEW.`rt_det`, 'dispositivo_det', NEW.`dispositivo_det`, 'atb_det', NEW.`atb_det`, 'atb_uso_det', NEW.`atb_uso_det`, 'acamado_det', NEW.`acamado_det`, 'exames_det', NEW.`exames_det`, 'oportunidades_det', NEW.`oportunidades_det`, 'lesoes_pele_det', NEW.`lesoes_pele_det`, 'tqt_det', NEW.`tqt_det`, 'sne_det', NEW.`sne_det`, 'gtt_det', NEW.`gtt_det`, 'svd_det', NEW.`svd_det`, 'dreno_det', NEW.`dreno_det`, 'medic_alto_custo_det', NEW.`medic_alto_custo_det`, 'qual_medicamento_det', NEW.`qual_medicamento_det`, 'liminar_det', NEW.`liminar_det`, 'paliativos_det', NEW.`paliativos_det`, 'parto_det', NEW.`parto_det`, 'braden_det', NEW.`braden_det`, 'oxigenio_hiperbarica_det', NEW.`oxigenio_hiperbarica_det`, 'dialise_det', NEW.`dialise_det`, 'hemoderivados_det', NEW.`hemoderivados_det`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_detalhes
AFTER UPDATE ON tb_detalhes
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_detalhes', 'UPDATE', NOW(), NEW.`id_detalhes`, JSON_OBJECT('id_detalhes', OLD.`id_detalhes`, 'fk_int_det', OLD.`fk_int_det`, 'fk_vis_det', OLD.`fk_vis_det`, 'curativo_det', OLD.`curativo_det`, 'dieta_det', OLD.`dieta_det`, 'nivel_consc_det', OLD.`nivel_consc_det`, 'oxig_det', OLD.`oxig_det`, 'oxig_uso_det', OLD.`oxig_uso_det`, 'qt_det', OLD.`qt_det`, 'rt_det', OLD.`rt_det`, 'dispositivo_det', OLD.`dispositivo_det`, 'atb_det', OLD.`atb_det`, 'atb_uso_det', OLD.`atb_uso_det`, 'acamado_det', OLD.`acamado_det`, 'exames_det', OLD.`exames_det`, 'oportunidades_det', OLD.`oportunidades_det`, 'lesoes_pele_det', OLD.`lesoes_pele_det`, 'tqt_det', OLD.`tqt_det`, 'sne_det', OLD.`sne_det`, 'gtt_det', OLD.`gtt_det`, 'svd_det', OLD.`svd_det`, 'dreno_det', OLD.`dreno_det`, 'medic_alto_custo_det', OLD.`medic_alto_custo_det`, 'qual_medicamento_det', OLD.`qual_medicamento_det`, 'liminar_det', OLD.`liminar_det`, 'paliativos_det', OLD.`paliativos_det`, 'parto_det', OLD.`parto_det`, 'braden_det', OLD.`braden_det`, 'oxigenio_hiperbarica_det', OLD.`oxigenio_hiperbarica_det`, 'dialise_det', OLD.`dialise_det`, 'hemoderivados_det', OLD.`hemoderivados_det`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_detalhes', NEW.`id_detalhes`, 'fk_int_det', NEW.`fk_int_det`, 'fk_vis_det', NEW.`fk_vis_det`, 'curativo_det', NEW.`curativo_det`, 'dieta_det', NEW.`dieta_det`, 'nivel_consc_det', NEW.`nivel_consc_det`, 'oxig_det', NEW.`oxig_det`, 'oxig_uso_det', NEW.`oxig_uso_det`, 'qt_det', NEW.`qt_det`, 'rt_det', NEW.`rt_det`, 'dispositivo_det', NEW.`dispositivo_det`, 'atb_det', NEW.`atb_det`, 'atb_uso_det', NEW.`atb_uso_det`, 'acamado_det', NEW.`acamado_det`, 'exames_det', NEW.`exames_det`, 'oportunidades_det', NEW.`oportunidades_det`, 'lesoes_pele_det', NEW.`lesoes_pele_det`, 'tqt_det', NEW.`tqt_det`, 'sne_det', NEW.`sne_det`, 'gtt_det', NEW.`gtt_det`, 'svd_det', NEW.`svd_det`, 'dreno_det', NEW.`dreno_det`, 'medic_alto_custo_det', NEW.`medic_alto_custo_det`, 'qual_medicamento_det', NEW.`qual_medicamento_det`, 'liminar_det', NEW.`liminar_det`, 'paliativos_det', NEW.`paliativos_det`, 'parto_det', NEW.`parto_det`, 'braden_det', NEW.`braden_det`, 'oxigenio_hiperbarica_det', NEW.`oxigenio_hiperbarica_det`, 'dialise_det', NEW.`dialise_det`, 'hemoderivados_det', NEW.`hemoderivados_det`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_detalhes
AFTER DELETE ON tb_detalhes
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_detalhes', 'DELETE', NOW(), OLD.`id_detalhes`, JSON_OBJECT('id_detalhes', OLD.`id_detalhes`, 'fk_int_det', OLD.`fk_int_det`, 'fk_vis_det', OLD.`fk_vis_det`, 'curativo_det', OLD.`curativo_det`, 'dieta_det', OLD.`dieta_det`, 'nivel_consc_det', OLD.`nivel_consc_det`, 'oxig_det', OLD.`oxig_det`, 'oxig_uso_det', OLD.`oxig_uso_det`, 'qt_det', OLD.`qt_det`, 'rt_det', OLD.`rt_det`, 'dispositivo_det', OLD.`dispositivo_det`, 'atb_det', OLD.`atb_det`, 'atb_uso_det', OLD.`atb_uso_det`, 'acamado_det', OLD.`acamado_det`, 'exames_det', OLD.`exames_det`, 'oportunidades_det', OLD.`oportunidades_det`, 'lesoes_pele_det', OLD.`lesoes_pele_det`, 'tqt_det', OLD.`tqt_det`, 'sne_det', OLD.`sne_det`, 'gtt_det', OLD.`gtt_det`, 'svd_det', OLD.`svd_det`, 'dreno_det', OLD.`dreno_det`, 'medic_alto_custo_det', OLD.`medic_alto_custo_det`, 'qual_medicamento_det', OLD.`qual_medicamento_det`, 'liminar_det', OLD.`liminar_det`, 'paliativos_det', OLD.`paliativos_det`, 'parto_det', OLD.`parto_det`, 'braden_det', OLD.`braden_det`, 'oxigenio_hiperbarica_det', OLD.`oxigenio_hiperbarica_det`, 'dialise_det', OLD.`dialise_det`, 'hemoderivados_det', OLD.`hemoderivados_det`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_estipulante
DROP TRIGGER IF EXISTS trg_log_insert_tb_estipulante;
DROP TRIGGER IF EXISTS trg_log_update_tb_estipulante;
DROP TRIGGER IF EXISTS trg_log_delete_tb_estipulante;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_estipulante
AFTER INSERT ON tb_estipulante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_estipulante', 'INSERT', NOW(), NEW.`id_estipulante`, NULL, JSON_OBJECT('id_estipulante', NEW.`id_estipulante`, 'nome_est', NEW.`nome_est`, 'endereco_est', NEW.`endereco_est`, 'bairro_est', NEW.`bairro_est`, 'numero_est', NEW.`numero_est`, 'cidade_est', NEW.`cidade_est`, 'estado_est', NEW.`estado_est`, 'cep_est', NEW.`cep_est`, 'coordenador_est', NEW.`coordenador_est`, 'coord_rh_est', NEW.`coord_rh_est`, 'telefone01_est', NEW.`telefone01_est`, 'telefone02_est', NEW.`telefone02_est`, 'email01_est', NEW.`email01_est`, 'email02_est', NEW.`email02_est`, 'data_create_est', NEW.`data_create_est`, 'fk_usuario_est', NEW.`fk_usuario_est`, 'cnpj_est', NEW.`cnpj_est`, 'usuario_create_est', NEW.`usuario_create_est`, 'logo_est', NEW.`logo_est`, 'deletado_est', NEW.`deletado_est`, 'nome_contato_est', NEW.`nome_contato_est`, 'nome_responsavel_est', NEW.`nome_responsavel_est`, 'email_contato_est', NEW.`email_contato_est`, 'email_responsavel_est', NEW.`email_responsavel_est`, 'telefone_contato_est', NEW.`telefone_contato_est`, 'telefone_responsavel_est', NEW.`telefone_responsavel_est`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_estipulante
AFTER UPDATE ON tb_estipulante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_estipulante', 'UPDATE', NOW(), NEW.`id_estipulante`, JSON_OBJECT('id_estipulante', OLD.`id_estipulante`, 'nome_est', OLD.`nome_est`, 'endereco_est', OLD.`endereco_est`, 'bairro_est', OLD.`bairro_est`, 'numero_est', OLD.`numero_est`, 'cidade_est', OLD.`cidade_est`, 'estado_est', OLD.`estado_est`, 'cep_est', OLD.`cep_est`, 'coordenador_est', OLD.`coordenador_est`, 'coord_rh_est', OLD.`coord_rh_est`, 'telefone01_est', OLD.`telefone01_est`, 'telefone02_est', OLD.`telefone02_est`, 'email01_est', OLD.`email01_est`, 'email02_est', OLD.`email02_est`, 'data_create_est', OLD.`data_create_est`, 'fk_usuario_est', OLD.`fk_usuario_est`, 'cnpj_est', OLD.`cnpj_est`, 'usuario_create_est', OLD.`usuario_create_est`, 'logo_est', OLD.`logo_est`, 'deletado_est', OLD.`deletado_est`, 'nome_contato_est', OLD.`nome_contato_est`, 'nome_responsavel_est', OLD.`nome_responsavel_est`, 'email_contato_est', OLD.`email_contato_est`, 'email_responsavel_est', OLD.`email_responsavel_est`, 'telefone_contato_est', OLD.`telefone_contato_est`, 'telefone_responsavel_est', OLD.`telefone_responsavel_est`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_estipulante', NEW.`id_estipulante`, 'nome_est', NEW.`nome_est`, 'endereco_est', NEW.`endereco_est`, 'bairro_est', NEW.`bairro_est`, 'numero_est', NEW.`numero_est`, 'cidade_est', NEW.`cidade_est`, 'estado_est', NEW.`estado_est`, 'cep_est', NEW.`cep_est`, 'coordenador_est', NEW.`coordenador_est`, 'coord_rh_est', NEW.`coord_rh_est`, 'telefone01_est', NEW.`telefone01_est`, 'telefone02_est', NEW.`telefone02_est`, 'email01_est', NEW.`email01_est`, 'email02_est', NEW.`email02_est`, 'data_create_est', NEW.`data_create_est`, 'fk_usuario_est', NEW.`fk_usuario_est`, 'cnpj_est', NEW.`cnpj_est`, 'usuario_create_est', NEW.`usuario_create_est`, 'logo_est', NEW.`logo_est`, 'deletado_est', NEW.`deletado_est`, 'nome_contato_est', NEW.`nome_contato_est`, 'nome_responsavel_est', NEW.`nome_responsavel_est`, 'email_contato_est', NEW.`email_contato_est`, 'email_responsavel_est', NEW.`email_responsavel_est`, 'telefone_contato_est', NEW.`telefone_contato_est`, 'telefone_responsavel_est', NEW.`telefone_responsavel_est`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_estipulante
AFTER DELETE ON tb_estipulante
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_estipulante', 'DELETE', NOW(), OLD.`id_estipulante`, JSON_OBJECT('id_estipulante', OLD.`id_estipulante`, 'nome_est', OLD.`nome_est`, 'endereco_est', OLD.`endereco_est`, 'bairro_est', OLD.`bairro_est`, 'numero_est', OLD.`numero_est`, 'cidade_est', OLD.`cidade_est`, 'estado_est', OLD.`estado_est`, 'cep_est', OLD.`cep_est`, 'coordenador_est', OLD.`coordenador_est`, 'coord_rh_est', OLD.`coord_rh_est`, 'telefone01_est', OLD.`telefone01_est`, 'telefone02_est', OLD.`telefone02_est`, 'email01_est', OLD.`email01_est`, 'email02_est', OLD.`email02_est`, 'data_create_est', OLD.`data_create_est`, 'fk_usuario_est', OLD.`fk_usuario_est`, 'cnpj_est', OLD.`cnpj_est`, 'usuario_create_est', OLD.`usuario_create_est`, 'logo_est', OLD.`logo_est`, 'deletado_est', OLD.`deletado_est`, 'nome_contato_est', OLD.`nome_contato_est`, 'nome_responsavel_est', OLD.`nome_responsavel_est`, 'email_contato_est', OLD.`email_contato_est`, 'email_responsavel_est', OLD.`email_responsavel_est`, 'telefone_contato_est', OLD.`telefone_contato_est`, 'telefone_responsavel_est', OLD.`telefone_responsavel_est`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_gestao
DROP TRIGGER IF EXISTS trg_log_insert_tb_gestao;
DROP TRIGGER IF EXISTS trg_log_update_tb_gestao;
DROP TRIGGER IF EXISTS trg_log_delete_tb_gestao;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_gestao
AFTER INSERT ON tb_gestao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_gestao', 'INSERT', NOW(), NEW.`id_gestao`, NULL, JSON_OBJECT('id_gestao', NEW.`id_gestao`, 'fk_internacao_ges', NEW.`fk_internacao_ges`, 'alto_custo_ges', NEW.`alto_custo_ges`, 'rel_alto_custo_ges', NEW.`rel_alto_custo_ges`, 'evento_adverso_ges', NEW.`evento_adverso_ges`, 'rel_evento_adverso_ges', NEW.`rel_evento_adverso_ges`, 'tipo_evento_adverso_gest', NEW.`tipo_evento_adverso_gest`, 'opme_ges', NEW.`opme_ges`, 'rel_opme_ges', NEW.`rel_opme_ges`, 'home_care_ges', NEW.`home_care_ges`, 'rel_home_care_ges', NEW.`rel_home_care_ges`, 'desospitalizacao_ges', NEW.`desospitalizacao_ges`, 'rel_desospitalizacao_ges', NEW.`rel_desospitalizacao_ges`, 'fk_user_ges', NEW.`fk_user_ges`, 'fk_visita_ges', NEW.`fk_visita_ges`, 'data_create_ges', NEW.`data_create_ges`, 'fk_usuario_ges', NEW.`fk_usuario_ges`, 'deletado_ges', NEW.`deletado_ges`, 'evento_sinalizado_ges', NEW.`evento_sinalizado_ges`, 'evento_discutido_ges', NEW.`evento_discutido_ges`, 'evento_negociado_ges', NEW.`evento_negociado_ges`, 'evento_valor_negoc_ges', NEW.`evento_valor_negoc_ges`, 'evento_prorrogar_ges', NEW.`evento_prorrogar_ges`, 'evento_fech_ges', NEW.`evento_fech_ges`, 'evento_retorno_qual_hosp_ges', NEW.`evento_retorno_qual_hosp_ges`, 'evento_classificado_hospital_ges', NEW.`evento_classificado_hospital_ges`, 'evento_data_ges', NEW.`evento_data_ges`, 'evento_encerrar_ges', NEW.`evento_encerrar_ges`, 'evento_impacto_financ_ges', NEW.`evento_impacto_financ_ges`, 'evento_prolongou_internacao_ges', NEW.`evento_prolongou_internacao_ges`, 'evento_concluido_ges', NEW.`evento_concluido_ges`, 'evento_classificacao_ges', NEW.`evento_classificacao_ges`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_gestao
AFTER UPDATE ON tb_gestao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_gestao', 'UPDATE', NOW(), NEW.`id_gestao`, JSON_OBJECT('id_gestao', OLD.`id_gestao`, 'fk_internacao_ges', OLD.`fk_internacao_ges`, 'alto_custo_ges', OLD.`alto_custo_ges`, 'rel_alto_custo_ges', OLD.`rel_alto_custo_ges`, 'evento_adverso_ges', OLD.`evento_adverso_ges`, 'rel_evento_adverso_ges', OLD.`rel_evento_adverso_ges`, 'tipo_evento_adverso_gest', OLD.`tipo_evento_adverso_gest`, 'opme_ges', OLD.`opme_ges`, 'rel_opme_ges', OLD.`rel_opme_ges`, 'home_care_ges', OLD.`home_care_ges`, 'rel_home_care_ges', OLD.`rel_home_care_ges`, 'desospitalizacao_ges', OLD.`desospitalizacao_ges`, 'rel_desospitalizacao_ges', OLD.`rel_desospitalizacao_ges`, 'fk_user_ges', OLD.`fk_user_ges`, 'fk_visita_ges', OLD.`fk_visita_ges`, 'data_create_ges', OLD.`data_create_ges`, 'fk_usuario_ges', OLD.`fk_usuario_ges`, 'deletado_ges', OLD.`deletado_ges`, 'evento_sinalizado_ges', OLD.`evento_sinalizado_ges`, 'evento_discutido_ges', OLD.`evento_discutido_ges`, 'evento_negociado_ges', OLD.`evento_negociado_ges`, 'evento_valor_negoc_ges', OLD.`evento_valor_negoc_ges`, 'evento_prorrogar_ges', OLD.`evento_prorrogar_ges`, 'evento_fech_ges', OLD.`evento_fech_ges`, 'evento_retorno_qual_hosp_ges', OLD.`evento_retorno_qual_hosp_ges`, 'evento_classificado_hospital_ges', OLD.`evento_classificado_hospital_ges`, 'evento_data_ges', OLD.`evento_data_ges`, 'evento_encerrar_ges', OLD.`evento_encerrar_ges`, 'evento_impacto_financ_ges', OLD.`evento_impacto_financ_ges`, 'evento_prolongou_internacao_ges', OLD.`evento_prolongou_internacao_ges`, 'evento_concluido_ges', OLD.`evento_concluido_ges`, 'evento_classificacao_ges', OLD.`evento_classificacao_ges`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_gestao', NEW.`id_gestao`, 'fk_internacao_ges', NEW.`fk_internacao_ges`, 'alto_custo_ges', NEW.`alto_custo_ges`, 'rel_alto_custo_ges', NEW.`rel_alto_custo_ges`, 'evento_adverso_ges', NEW.`evento_adverso_ges`, 'rel_evento_adverso_ges', NEW.`rel_evento_adverso_ges`, 'tipo_evento_adverso_gest', NEW.`tipo_evento_adverso_gest`, 'opme_ges', NEW.`opme_ges`, 'rel_opme_ges', NEW.`rel_opme_ges`, 'home_care_ges', NEW.`home_care_ges`, 'rel_home_care_ges', NEW.`rel_home_care_ges`, 'desospitalizacao_ges', NEW.`desospitalizacao_ges`, 'rel_desospitalizacao_ges', NEW.`rel_desospitalizacao_ges`, 'fk_user_ges', NEW.`fk_user_ges`, 'fk_visita_ges', NEW.`fk_visita_ges`, 'data_create_ges', NEW.`data_create_ges`, 'fk_usuario_ges', NEW.`fk_usuario_ges`, 'deletado_ges', NEW.`deletado_ges`, 'evento_sinalizado_ges', NEW.`evento_sinalizado_ges`, 'evento_discutido_ges', NEW.`evento_discutido_ges`, 'evento_negociado_ges', NEW.`evento_negociado_ges`, 'evento_valor_negoc_ges', NEW.`evento_valor_negoc_ges`, 'evento_prorrogar_ges', NEW.`evento_prorrogar_ges`, 'evento_fech_ges', NEW.`evento_fech_ges`, 'evento_retorno_qual_hosp_ges', NEW.`evento_retorno_qual_hosp_ges`, 'evento_classificado_hospital_ges', NEW.`evento_classificado_hospital_ges`, 'evento_data_ges', NEW.`evento_data_ges`, 'evento_encerrar_ges', NEW.`evento_encerrar_ges`, 'evento_impacto_financ_ges', NEW.`evento_impacto_financ_ges`, 'evento_prolongou_internacao_ges', NEW.`evento_prolongou_internacao_ges`, 'evento_concluido_ges', NEW.`evento_concluido_ges`, 'evento_classificacao_ges', NEW.`evento_classificacao_ges`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_gestao
AFTER DELETE ON tb_gestao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_gestao', 'DELETE', NOW(), OLD.`id_gestao`, JSON_OBJECT('id_gestao', OLD.`id_gestao`, 'fk_internacao_ges', OLD.`fk_internacao_ges`, 'alto_custo_ges', OLD.`alto_custo_ges`, 'rel_alto_custo_ges', OLD.`rel_alto_custo_ges`, 'evento_adverso_ges', OLD.`evento_adverso_ges`, 'rel_evento_adverso_ges', OLD.`rel_evento_adverso_ges`, 'tipo_evento_adverso_gest', OLD.`tipo_evento_adverso_gest`, 'opme_ges', OLD.`opme_ges`, 'rel_opme_ges', OLD.`rel_opme_ges`, 'home_care_ges', OLD.`home_care_ges`, 'rel_home_care_ges', OLD.`rel_home_care_ges`, 'desospitalizacao_ges', OLD.`desospitalizacao_ges`, 'rel_desospitalizacao_ges', OLD.`rel_desospitalizacao_ges`, 'fk_user_ges', OLD.`fk_user_ges`, 'fk_visita_ges', OLD.`fk_visita_ges`, 'data_create_ges', OLD.`data_create_ges`, 'fk_usuario_ges', OLD.`fk_usuario_ges`, 'deletado_ges', OLD.`deletado_ges`, 'evento_sinalizado_ges', OLD.`evento_sinalizado_ges`, 'evento_discutido_ges', OLD.`evento_discutido_ges`, 'evento_negociado_ges', OLD.`evento_negociado_ges`, 'evento_valor_negoc_ges', OLD.`evento_valor_negoc_ges`, 'evento_prorrogar_ges', OLD.`evento_prorrogar_ges`, 'evento_fech_ges', OLD.`evento_fech_ges`, 'evento_retorno_qual_hosp_ges', OLD.`evento_retorno_qual_hosp_ges`, 'evento_classificado_hospital_ges', OLD.`evento_classificado_hospital_ges`, 'evento_data_ges', OLD.`evento_data_ges`, 'evento_encerrar_ges', OLD.`evento_encerrar_ges`, 'evento_impacto_financ_ges', OLD.`evento_impacto_financ_ges`, 'evento_prolongou_internacao_ges', OLD.`evento_prolongou_internacao_ges`, 'evento_concluido_ges', OLD.`evento_concluido_ges`, 'evento_classificacao_ges', OLD.`evento_classificacao_ges`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_hospital
DROP TRIGGER IF EXISTS trg_log_insert_tb_hospital;
DROP TRIGGER IF EXISTS trg_log_update_tb_hospital;
DROP TRIGGER IF EXISTS trg_log_delete_tb_hospital;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_hospital
AFTER INSERT ON tb_hospital
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospital', 'INSERT', NOW(), NEW.`id_hospital`, NULL, JSON_OBJECT('id_hospital', NEW.`id_hospital`, 'nome_hosp', NEW.`nome_hosp`, 'endereco_hosp', NEW.`endereco_hosp`, 'bairro_hosp', NEW.`bairro_hosp`, 'numero_hosp', NEW.`numero_hosp`, 'cidade_hosp', NEW.`cidade_hosp`, 'estado_hosp', NEW.`estado_hosp`, 'longitude_hosp', NEW.`longitude_hosp`, 'latitude_hosp', NEW.`latitude_hosp`, 'cep_hosp', NEW.`cep_hosp`, 'coordenador_medico_hosp', NEW.`coordenador_medico_hosp`, 'coordenador_fat_hosp', NEW.`coordenador_fat_hosp`, 'telefone01_hosp', NEW.`telefone01_hosp`, 'telefone02_hosp', NEW.`telefone02_hosp`, 'email01_hosp', NEW.`email01_hosp`, 'email02_hosp', NEW.`email02_hosp`, 'diretor_hosp', NEW.`diretor_hosp`, 'usuario_create_hosp', NEW.`usuario_create_hosp`, 'fk_usuario_hosp', NEW.`fk_usuario_hosp`, 'cnpj_hosp', NEW.`cnpj_hosp`, 'logo2_hosp', NEW.`logo2_hosp`, 'logo_hosp', NEW.`logo_hosp`, 'ativo_hosp', NEW.`ativo_hosp`, 'data_create_hosp', NEW.`data_create_hosp`, 'deletado_hosp', NEW.`deletado_hosp`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_hospital
AFTER UPDATE ON tb_hospital
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospital', 'UPDATE', NOW(), NEW.`id_hospital`, JSON_OBJECT('id_hospital', OLD.`id_hospital`, 'nome_hosp', OLD.`nome_hosp`, 'endereco_hosp', OLD.`endereco_hosp`, 'bairro_hosp', OLD.`bairro_hosp`, 'numero_hosp', OLD.`numero_hosp`, 'cidade_hosp', OLD.`cidade_hosp`, 'estado_hosp', OLD.`estado_hosp`, 'longitude_hosp', OLD.`longitude_hosp`, 'latitude_hosp', OLD.`latitude_hosp`, 'cep_hosp', OLD.`cep_hosp`, 'coordenador_medico_hosp', OLD.`coordenador_medico_hosp`, 'coordenador_fat_hosp', OLD.`coordenador_fat_hosp`, 'telefone01_hosp', OLD.`telefone01_hosp`, 'telefone02_hosp', OLD.`telefone02_hosp`, 'email01_hosp', OLD.`email01_hosp`, 'email02_hosp', OLD.`email02_hosp`, 'diretor_hosp', OLD.`diretor_hosp`, 'usuario_create_hosp', OLD.`usuario_create_hosp`, 'fk_usuario_hosp', OLD.`fk_usuario_hosp`, 'cnpj_hosp', OLD.`cnpj_hosp`, 'logo2_hosp', OLD.`logo2_hosp`, 'logo_hosp', OLD.`logo_hosp`, 'ativo_hosp', OLD.`ativo_hosp`, 'data_create_hosp', OLD.`data_create_hosp`, 'deletado_hosp', OLD.`deletado_hosp`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_hospital', NEW.`id_hospital`, 'nome_hosp', NEW.`nome_hosp`, 'endereco_hosp', NEW.`endereco_hosp`, 'bairro_hosp', NEW.`bairro_hosp`, 'numero_hosp', NEW.`numero_hosp`, 'cidade_hosp', NEW.`cidade_hosp`, 'estado_hosp', NEW.`estado_hosp`, 'longitude_hosp', NEW.`longitude_hosp`, 'latitude_hosp', NEW.`latitude_hosp`, 'cep_hosp', NEW.`cep_hosp`, 'coordenador_medico_hosp', NEW.`coordenador_medico_hosp`, 'coordenador_fat_hosp', NEW.`coordenador_fat_hosp`, 'telefone01_hosp', NEW.`telefone01_hosp`, 'telefone02_hosp', NEW.`telefone02_hosp`, 'email01_hosp', NEW.`email01_hosp`, 'email02_hosp', NEW.`email02_hosp`, 'diretor_hosp', NEW.`diretor_hosp`, 'usuario_create_hosp', NEW.`usuario_create_hosp`, 'fk_usuario_hosp', NEW.`fk_usuario_hosp`, 'cnpj_hosp', NEW.`cnpj_hosp`, 'logo2_hosp', NEW.`logo2_hosp`, 'logo_hosp', NEW.`logo_hosp`, 'ativo_hosp', NEW.`ativo_hosp`, 'data_create_hosp', NEW.`data_create_hosp`, 'deletado_hosp', NEW.`deletado_hosp`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_hospital
AFTER DELETE ON tb_hospital
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospital', 'DELETE', NOW(), OLD.`id_hospital`, JSON_OBJECT('id_hospital', OLD.`id_hospital`, 'nome_hosp', OLD.`nome_hosp`, 'endereco_hosp', OLD.`endereco_hosp`, 'bairro_hosp', OLD.`bairro_hosp`, 'numero_hosp', OLD.`numero_hosp`, 'cidade_hosp', OLD.`cidade_hosp`, 'estado_hosp', OLD.`estado_hosp`, 'longitude_hosp', OLD.`longitude_hosp`, 'latitude_hosp', OLD.`latitude_hosp`, 'cep_hosp', OLD.`cep_hosp`, 'coordenador_medico_hosp', OLD.`coordenador_medico_hosp`, 'coordenador_fat_hosp', OLD.`coordenador_fat_hosp`, 'telefone01_hosp', OLD.`telefone01_hosp`, 'telefone02_hosp', OLD.`telefone02_hosp`, 'email01_hosp', OLD.`email01_hosp`, 'email02_hosp', OLD.`email02_hosp`, 'diretor_hosp', OLD.`diretor_hosp`, 'usuario_create_hosp', OLD.`usuario_create_hosp`, 'fk_usuario_hosp', OLD.`fk_usuario_hosp`, 'cnpj_hosp', OLD.`cnpj_hosp`, 'logo2_hosp', OLD.`logo2_hosp`, 'logo_hosp', OLD.`logo_hosp`, 'ativo_hosp', OLD.`ativo_hosp`, 'data_create_hosp', OLD.`data_create_hosp`, 'deletado_hosp', OLD.`deletado_hosp`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_hospitalUser
DROP TRIGGER IF EXISTS trg_log_insert_tb_hospitalUser;
DROP TRIGGER IF EXISTS trg_log_update_tb_hospitalUser;
DROP TRIGGER IF EXISTS trg_log_delete_tb_hospitalUser;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_hospitalUser
AFTER INSERT ON tb_hospitalUser
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospitalUser', 'INSERT', NOW(), NEW.`id_hospitalUser`, NULL, JSON_OBJECT('id_hospitalUser', NEW.`id_hospitalUser`, 'fk_usuario_hosp', NEW.`fk_usuario_hosp`, 'fk_hospital_user', NEW.`fk_hospital_user`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_hospitalUser
AFTER UPDATE ON tb_hospitalUser
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospitalUser', 'UPDATE', NOW(), NEW.`id_hospitalUser`, JSON_OBJECT('id_hospitalUser', OLD.`id_hospitalUser`, 'fk_usuario_hosp', OLD.`fk_usuario_hosp`, 'fk_hospital_user', OLD.`fk_hospital_user`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_hospitalUser', NEW.`id_hospitalUser`, 'fk_usuario_hosp', NEW.`fk_usuario_hosp`, 'fk_hospital_user', NEW.`fk_hospital_user`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_hospitalUser
AFTER DELETE ON tb_hospitalUser
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_hospitalUser', 'DELETE', NOW(), OLD.`id_hospitalUser`, JSON_OBJECT('id_hospitalUser', OLD.`id_hospitalUser`, 'fk_usuario_hosp', OLD.`fk_usuario_hosp`, 'fk_hospital_user', OLD.`fk_hospital_user`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_imagens
DROP TRIGGER IF EXISTS trg_log_insert_tb_imagens;
DROP TRIGGER IF EXISTS trg_log_update_tb_imagens;
DROP TRIGGER IF EXISTS trg_log_delete_tb_imagens;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_imagens
AFTER INSERT ON tb_imagens
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_imagens', 'INSERT', NOW(), NEW.`id_imagem`, NULL, JSON_OBJECT('id_imagem', NEW.`id_imagem`, 'fk_imagem', NEW.`fk_imagem`, 'imagem_img', NEW.`imagem_img`, 'imagem_name_img', NEW.`imagem_name_img`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_imagens
AFTER UPDATE ON tb_imagens
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_imagens', 'UPDATE', NOW(), NEW.`id_imagem`, JSON_OBJECT('id_imagem', OLD.`id_imagem`, 'fk_imagem', OLD.`fk_imagem`, 'imagem_img', OLD.`imagem_img`, 'imagem_name_img', OLD.`imagem_name_img`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_imagem', NEW.`id_imagem`, 'fk_imagem', NEW.`fk_imagem`, 'imagem_img', NEW.`imagem_img`, 'imagem_name_img', NEW.`imagem_name_img`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_imagens
AFTER DELETE ON tb_imagens
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_imagens', 'DELETE', NOW(), OLD.`id_imagem`, JSON_OBJECT('id_imagem', OLD.`id_imagem`, 'fk_imagem', OLD.`fk_imagem`, 'imagem_img', OLD.`imagem_img`, 'imagem_name_img', OLD.`imagem_name_img`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_internacao
DROP TRIGGER IF EXISTS trg_log_insert_tb_internacao;
DROP TRIGGER IF EXISTS trg_log_update_tb_internacao;
DROP TRIGGER IF EXISTS trg_log_delete_tb_internacao;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_internacao
AFTER INSERT ON tb_internacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao', 'INSERT', NOW(), NEW.`id_internacao`, NULL, JSON_OBJECT('id_internacao', NEW.`id_internacao`, 'fk_paciente_int', NEW.`fk_paciente_int`, 'fk_hospital_int', NEW.`fk_hospital_int`, 'fk_patologia_int', NEW.`fk_patologia_int`, 'fk_antecedente', NEW.`fk_antecedente`, 'usuario_create_int', NEW.`usuario_create_int`, 'data_intern_int', NEW.`data_intern_int`, 'data_lancamento_int', NEW.`data_lancamento_int`, 'hora_intern_int', NEW.`hora_intern_int`, 'titular_int', NEW.`titular_int`, 'especialidade_int', NEW.`especialidade_int`, 'acomodacao_int', NEW.`acomodacao_int`, 'modo_internacao_int', NEW.`modo_internacao_int`, 'tipo_admissao_int', NEW.`tipo_admissao_int`, 'grupo_patologia_int', NEW.`grupo_patologia_int`, 'data_visita_int', NEW.`data_visita_int`, 'internado_int', NEW.`internado_int`, 'senha_int', NEW.`senha_int`, 'internacao_ativa_int', NEW.`internacao_ativa_int`, 'acoes_int', NEW.`acoes_int`, 'rel_int', NEW.`rel_int`, 'internacao_uti_int', NEW.`internacao_uti_int`, 'visita_auditor_prof_med', NEW.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', NEW.`visita_auditor_prof_enf`, 'internado_uti_int', NEW.`internado_uti_int`, 'fk_patologia2', NEW.`fk_patologia2`, 'data_create_int', NEW.`data_create_int`, 'primeira_vis_int', NEW.`primeira_vis_int`, 'visita_no_int', NEW.`visita_no_int`, 'visita_enf_int', NEW.`visita_enf_int`, 'visita_med_int', NEW.`visita_med_int`, 'fk_usuario_int', NEW.`fk_usuario_int`, 'crm_int', NEW.`crm_int`, 'censo_int', NEW.`censo_int`, 'conta_auditada_int', NEW.`conta_auditada_int`, 'conta_em_analise_int', NEW.`conta_em_analise_int`, 'programacao_int', NEW.`programacao_int`, 'deletado_int', NEW.`deletado_int`, 'origem_int', NEW.`origem_int`, 'int_pertinente_int', NEW.`int_pertinente_int`, 'rel_pertinente_int', NEW.`rel_pertinente_int`, 'updated_at', NEW.`updated_at`, 'num_atendimento_int', NEW.`num_atendimento_int`, 'timer_int', NEW.`timer_int`, 'forecast_total_days', NEW.`forecast_total_days`, 'forecast_lower_days', NEW.`forecast_lower_days`, 'forecast_upper_days', NEW.`forecast_upper_days`, 'forecast_generated_at', NEW.`forecast_generated_at`, 'forecast_model', NEW.`forecast_model`, 'forecast_confidence', NEW.`forecast_confidence`, 'fk_cid_int', NEW.`fk_cid_int`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_internacao
AFTER UPDATE ON tb_internacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao', 'UPDATE', NOW(), NEW.`id_internacao`, JSON_OBJECT('id_internacao', OLD.`id_internacao`, 'fk_paciente_int', OLD.`fk_paciente_int`, 'fk_hospital_int', OLD.`fk_hospital_int`, 'fk_patologia_int', OLD.`fk_patologia_int`, 'fk_antecedente', OLD.`fk_antecedente`, 'usuario_create_int', OLD.`usuario_create_int`, 'data_intern_int', OLD.`data_intern_int`, 'data_lancamento_int', OLD.`data_lancamento_int`, 'hora_intern_int', OLD.`hora_intern_int`, 'titular_int', OLD.`titular_int`, 'especialidade_int', OLD.`especialidade_int`, 'acomodacao_int', OLD.`acomodacao_int`, 'modo_internacao_int', OLD.`modo_internacao_int`, 'tipo_admissao_int', OLD.`tipo_admissao_int`, 'grupo_patologia_int', OLD.`grupo_patologia_int`, 'data_visita_int', OLD.`data_visita_int`, 'internado_int', OLD.`internado_int`, 'senha_int', OLD.`senha_int`, 'internacao_ativa_int', OLD.`internacao_ativa_int`, 'acoes_int', OLD.`acoes_int`, 'rel_int', OLD.`rel_int`, 'internacao_uti_int', OLD.`internacao_uti_int`, 'visita_auditor_prof_med', OLD.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', OLD.`visita_auditor_prof_enf`, 'internado_uti_int', OLD.`internado_uti_int`, 'fk_patologia2', OLD.`fk_patologia2`, 'data_create_int', OLD.`data_create_int`, 'primeira_vis_int', OLD.`primeira_vis_int`, 'visita_no_int', OLD.`visita_no_int`, 'visita_enf_int', OLD.`visita_enf_int`, 'visita_med_int', OLD.`visita_med_int`, 'fk_usuario_int', OLD.`fk_usuario_int`, 'crm_int', OLD.`crm_int`, 'censo_int', OLD.`censo_int`, 'conta_auditada_int', OLD.`conta_auditada_int`, 'conta_em_analise_int', OLD.`conta_em_analise_int`, 'programacao_int', OLD.`programacao_int`, 'deletado_int', OLD.`deletado_int`, 'origem_int', OLD.`origem_int`, 'int_pertinente_int', OLD.`int_pertinente_int`, 'rel_pertinente_int', OLD.`rel_pertinente_int`, 'updated_at', OLD.`updated_at`, 'num_atendimento_int', OLD.`num_atendimento_int`, 'timer_int', OLD.`timer_int`, 'forecast_total_days', OLD.`forecast_total_days`, 'forecast_lower_days', OLD.`forecast_lower_days`, 'forecast_upper_days', OLD.`forecast_upper_days`, 'forecast_generated_at', OLD.`forecast_generated_at`, 'forecast_model', OLD.`forecast_model`, 'forecast_confidence', OLD.`forecast_confidence`, 'fk_cid_int', OLD.`fk_cid_int`), JSON_OBJECT('id_internacao', NEW.`id_internacao`, 'fk_paciente_int', NEW.`fk_paciente_int`, 'fk_hospital_int', NEW.`fk_hospital_int`, 'fk_patologia_int', NEW.`fk_patologia_int`, 'fk_antecedente', NEW.`fk_antecedente`, 'usuario_create_int', NEW.`usuario_create_int`, 'data_intern_int', NEW.`data_intern_int`, 'data_lancamento_int', NEW.`data_lancamento_int`, 'hora_intern_int', NEW.`hora_intern_int`, 'titular_int', NEW.`titular_int`, 'especialidade_int', NEW.`especialidade_int`, 'acomodacao_int', NEW.`acomodacao_int`, 'modo_internacao_int', NEW.`modo_internacao_int`, 'tipo_admissao_int', NEW.`tipo_admissao_int`, 'grupo_patologia_int', NEW.`grupo_patologia_int`, 'data_visita_int', NEW.`data_visita_int`, 'internado_int', NEW.`internado_int`, 'senha_int', NEW.`senha_int`, 'internacao_ativa_int', NEW.`internacao_ativa_int`, 'acoes_int', NEW.`acoes_int`, 'rel_int', NEW.`rel_int`, 'internacao_uti_int', NEW.`internacao_uti_int`, 'visita_auditor_prof_med', NEW.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', NEW.`visita_auditor_prof_enf`, 'internado_uti_int', NEW.`internado_uti_int`, 'fk_patologia2', NEW.`fk_patologia2`, 'data_create_int', NEW.`data_create_int`, 'primeira_vis_int', NEW.`primeira_vis_int`, 'visita_no_int', NEW.`visita_no_int`, 'visita_enf_int', NEW.`visita_enf_int`, 'visita_med_int', NEW.`visita_med_int`, 'fk_usuario_int', NEW.`fk_usuario_int`, 'crm_int', NEW.`crm_int`, 'censo_int', NEW.`censo_int`, 'conta_auditada_int', NEW.`conta_auditada_int`, 'conta_em_analise_int', NEW.`conta_em_analise_int`, 'programacao_int', NEW.`programacao_int`, 'deletado_int', NEW.`deletado_int`, 'origem_int', NEW.`origem_int`, 'int_pertinente_int', NEW.`int_pertinente_int`, 'rel_pertinente_int', NEW.`rel_pertinente_int`, 'updated_at', NEW.`updated_at`, 'num_atendimento_int', NEW.`num_atendimento_int`, 'timer_int', NEW.`timer_int`, 'forecast_total_days', NEW.`forecast_total_days`, 'forecast_lower_days', NEW.`forecast_lower_days`, 'forecast_upper_days', NEW.`forecast_upper_days`, 'forecast_generated_at', NEW.`forecast_generated_at`, 'forecast_model', NEW.`forecast_model`, 'forecast_confidence', NEW.`forecast_confidence`, 'fk_cid_int', NEW.`fk_cid_int`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_internacao
AFTER DELETE ON tb_internacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao', 'DELETE', NOW(), OLD.`id_internacao`, JSON_OBJECT('id_internacao', OLD.`id_internacao`, 'fk_paciente_int', OLD.`fk_paciente_int`, 'fk_hospital_int', OLD.`fk_hospital_int`, 'fk_patologia_int', OLD.`fk_patologia_int`, 'fk_antecedente', OLD.`fk_antecedente`, 'usuario_create_int', OLD.`usuario_create_int`, 'data_intern_int', OLD.`data_intern_int`, 'data_lancamento_int', OLD.`data_lancamento_int`, 'hora_intern_int', OLD.`hora_intern_int`, 'titular_int', OLD.`titular_int`, 'especialidade_int', OLD.`especialidade_int`, 'acomodacao_int', OLD.`acomodacao_int`, 'modo_internacao_int', OLD.`modo_internacao_int`, 'tipo_admissao_int', OLD.`tipo_admissao_int`, 'grupo_patologia_int', OLD.`grupo_patologia_int`, 'data_visita_int', OLD.`data_visita_int`, 'internado_int', OLD.`internado_int`, 'senha_int', OLD.`senha_int`, 'internacao_ativa_int', OLD.`internacao_ativa_int`, 'acoes_int', OLD.`acoes_int`, 'rel_int', OLD.`rel_int`, 'internacao_uti_int', OLD.`internacao_uti_int`, 'visita_auditor_prof_med', OLD.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', OLD.`visita_auditor_prof_enf`, 'internado_uti_int', OLD.`internado_uti_int`, 'fk_patologia2', OLD.`fk_patologia2`, 'data_create_int', OLD.`data_create_int`, 'primeira_vis_int', OLD.`primeira_vis_int`, 'visita_no_int', OLD.`visita_no_int`, 'visita_enf_int', OLD.`visita_enf_int`, 'visita_med_int', OLD.`visita_med_int`, 'fk_usuario_int', OLD.`fk_usuario_int`, 'crm_int', OLD.`crm_int`, 'censo_int', OLD.`censo_int`, 'conta_auditada_int', OLD.`conta_auditada_int`, 'conta_em_analise_int', OLD.`conta_em_analise_int`, 'programacao_int', OLD.`programacao_int`, 'deletado_int', OLD.`deletado_int`, 'origem_int', OLD.`origem_int`, 'int_pertinente_int', OLD.`int_pertinente_int`, 'rel_pertinente_int', OLD.`rel_pertinente_int`, 'updated_at', OLD.`updated_at`, 'num_atendimento_int', OLD.`num_atendimento_int`, 'timer_int', OLD.`timer_int`, 'forecast_total_days', OLD.`forecast_total_days`, 'forecast_lower_days', OLD.`forecast_lower_days`, 'forecast_upper_days', OLD.`forecast_upper_days`, 'forecast_generated_at', OLD.`forecast_generated_at`, 'forecast_model', OLD.`forecast_model`, 'forecast_confidence', OLD.`forecast_confidence`, 'fk_cid_int', OLD.`fk_cid_int`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_internacao_arquivo
DROP TRIGGER IF EXISTS trg_log_insert_tb_internacao_arquivo;
DROP TRIGGER IF EXISTS trg_log_update_tb_internacao_arquivo;
DROP TRIGGER IF EXISTS trg_log_delete_tb_internacao_arquivo;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_internacao_arquivo
AFTER INSERT ON tb_internacao_arquivo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao_arquivo', 'INSERT', NOW(), NEW.`idtb_intern_arquivo`, NULL, JSON_OBJECT('idtb_intern_arquivo', NEW.`idtb_intern_arquivo`, 'id_internacao', NEW.`id_internacao`, 'nome_arquivo', NEW.`nome_arquivo`, 'arquivo', NEW.`arquivo`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_internacao_arquivo
AFTER UPDATE ON tb_internacao_arquivo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao_arquivo', 'UPDATE', NOW(), NEW.`idtb_intern_arquivo`, JSON_OBJECT('idtb_intern_arquivo', OLD.`idtb_intern_arquivo`, 'id_internacao', OLD.`id_internacao`, 'nome_arquivo', OLD.`nome_arquivo`, 'arquivo', OLD.`arquivo`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('idtb_intern_arquivo', NEW.`idtb_intern_arquivo`, 'id_internacao', NEW.`id_internacao`, 'nome_arquivo', NEW.`nome_arquivo`, 'arquivo', NEW.`arquivo`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_internacao_arquivo
AFTER DELETE ON tb_internacao_arquivo
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_internacao_arquivo', 'DELETE', NOW(), OLD.`idtb_intern_arquivo`, JSON_OBJECT('idtb_intern_arquivo', OLD.`idtb_intern_arquivo`, 'id_internacao', OLD.`id_internacao`, 'nome_arquivo', OLD.`nome_arquivo`, 'arquivo', OLD.`arquivo`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_intern_antec
DROP TRIGGER IF EXISTS trg_log_insert_tb_intern_antec;
DROP TRIGGER IF EXISTS trg_log_update_tb_intern_antec;
DROP TRIGGER IF EXISTS trg_log_delete_tb_intern_antec;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_intern_antec
AFTER INSERT ON tb_intern_antec
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_intern_antec', 'INSERT', NOW(), NEW.`id_intern_antec`, NULL, JSON_OBJECT('id_intern_antec', NEW.`id_intern_antec`, 'intern_antec_ant_int', NEW.`intern_antec_ant_int`, 'fK_internacao_ant_int', NEW.`fK_internacao_ant_int`, 'fk_id_paciente', NEW.`fk_id_paciente`, 'fk_internacao_vis', NEW.`fk_internacao_vis`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_intern_antec
AFTER UPDATE ON tb_intern_antec
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_intern_antec', 'UPDATE', NOW(), NEW.`id_intern_antec`, JSON_OBJECT('id_intern_antec', OLD.`id_intern_antec`, 'intern_antec_ant_int', OLD.`intern_antec_ant_int`, 'fK_internacao_ant_int', OLD.`fK_internacao_ant_int`, 'fk_id_paciente', OLD.`fk_id_paciente`, 'fk_internacao_vis', OLD.`fk_internacao_vis`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_intern_antec', NEW.`id_intern_antec`, 'intern_antec_ant_int', NEW.`intern_antec_ant_int`, 'fK_internacao_ant_int', NEW.`fK_internacao_ant_int`, 'fk_id_paciente', NEW.`fk_id_paciente`, 'fk_internacao_vis', NEW.`fk_internacao_vis`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_intern_antec
AFTER DELETE ON tb_intern_antec
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_intern_antec', 'DELETE', NOW(), OLD.`id_intern_antec`, JSON_OBJECT('id_intern_antec', OLD.`id_intern_antec`, 'intern_antec_ant_int', OLD.`intern_antec_ant_int`, 'fK_internacao_ant_int', OLD.`fK_internacao_ant_int`, 'fk_id_paciente', OLD.`fk_id_paciente`, 'fk_internacao_vis', OLD.`fk_internacao_vis`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_mensagem
DROP TRIGGER IF EXISTS trg_log_insert_tb_mensagem;
DROP TRIGGER IF EXISTS trg_log_update_tb_mensagem;
DROP TRIGGER IF EXISTS trg_log_delete_tb_mensagem;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_mensagem
AFTER INSERT ON tb_mensagem
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_mensagem', 'INSERT', NOW(), NEW.`id_mensagem`, NULL, JSON_OBJECT('id_mensagem', NEW.`id_mensagem`, 'de_usuario', NEW.`de_usuario`, 'para_usuario', NEW.`para_usuario`, 'mensagem', NEW.`mensagem`, 'data_mensagem', NEW.`data_mensagem`, 'vista', NEW.`vista`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_mensagem
AFTER UPDATE ON tb_mensagem
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_mensagem', 'UPDATE', NOW(), NEW.`id_mensagem`, JSON_OBJECT('id_mensagem', OLD.`id_mensagem`, 'de_usuario', OLD.`de_usuario`, 'para_usuario', OLD.`para_usuario`, 'mensagem', OLD.`mensagem`, 'data_mensagem', OLD.`data_mensagem`, 'vista', OLD.`vista`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_mensagem', NEW.`id_mensagem`, 'de_usuario', NEW.`de_usuario`, 'para_usuario', NEW.`para_usuario`, 'mensagem', NEW.`mensagem`, 'data_mensagem', NEW.`data_mensagem`, 'vista', NEW.`vista`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_mensagem
AFTER DELETE ON tb_mensagem
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_mensagem', 'DELETE', NOW(), OLD.`id_mensagem`, JSON_OBJECT('id_mensagem', OLD.`id_mensagem`, 'de_usuario', OLD.`de_usuario`, 'para_usuario', OLD.`para_usuario`, 'mensagem', OLD.`mensagem`, 'data_mensagem', OLD.`data_mensagem`, 'vista', OLD.`vista`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_negociacao
DROP TRIGGER IF EXISTS trg_log_insert_tb_negociacao;
DROP TRIGGER IF EXISTS trg_log_update_tb_negociacao;
DROP TRIGGER IF EXISTS trg_log_delete_tb_negociacao;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_negociacao
AFTER INSERT ON tb_negociacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_negociacao', 'INSERT', NOW(), NEW.`id_negociacao`, NULL, JSON_OBJECT('fk_id_int', NEW.`fk_id_int`, 'id_negociacao', NEW.`id_negociacao`, 'troca_de', NEW.`troca_de`, 'fk_visita_neg', NEW.`fk_visita_neg`, 'fk_usuario_neg', NEW.`fk_usuario_neg`, 'deletado_neg', NEW.`deletado_neg`, 'troca_para', NEW.`troca_para`, 'qtd', NEW.`qtd`, 'saving', NEW.`saving`, 'data_inicio_neg', NEW.`data_inicio_neg`, 'data_fim_neg', NEW.`data_fim_neg`, 'tipo_negociacao', NEW.`tipo_negociacao`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_negociacao
AFTER UPDATE ON tb_negociacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_negociacao', 'UPDATE', NOW(), NEW.`id_negociacao`, JSON_OBJECT('fk_id_int', OLD.`fk_id_int`, 'id_negociacao', OLD.`id_negociacao`, 'troca_de', OLD.`troca_de`, 'fk_visita_neg', OLD.`fk_visita_neg`, 'fk_usuario_neg', OLD.`fk_usuario_neg`, 'deletado_neg', OLD.`deletado_neg`, 'troca_para', OLD.`troca_para`, 'qtd', OLD.`qtd`, 'saving', OLD.`saving`, 'data_inicio_neg', OLD.`data_inicio_neg`, 'data_fim_neg', OLD.`data_fim_neg`, 'tipo_negociacao', OLD.`tipo_negociacao`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('fk_id_int', NEW.`fk_id_int`, 'id_negociacao', NEW.`id_negociacao`, 'troca_de', NEW.`troca_de`, 'fk_visita_neg', NEW.`fk_visita_neg`, 'fk_usuario_neg', NEW.`fk_usuario_neg`, 'deletado_neg', NEW.`deletado_neg`, 'troca_para', NEW.`troca_para`, 'qtd', NEW.`qtd`, 'saving', NEW.`saving`, 'data_inicio_neg', NEW.`data_inicio_neg`, 'data_fim_neg', NEW.`data_fim_neg`, 'tipo_negociacao', NEW.`tipo_negociacao`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_negociacao
AFTER DELETE ON tb_negociacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_negociacao', 'DELETE', NOW(), OLD.`id_negociacao`, JSON_OBJECT('fk_id_int', OLD.`fk_id_int`, 'id_negociacao', OLD.`id_negociacao`, 'troca_de', OLD.`troca_de`, 'fk_visita_neg', OLD.`fk_visita_neg`, 'fk_usuario_neg', OLD.`fk_usuario_neg`, 'deletado_neg', OLD.`deletado_neg`, 'troca_para', OLD.`troca_para`, 'qtd', OLD.`qtd`, 'saving', OLD.`saving`, 'data_inicio_neg', OLD.`data_inicio_neg`, 'data_fim_neg', OLD.`data_fim_neg`, 'tipo_negociacao', OLD.`tipo_negociacao`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_paciente
DROP TRIGGER IF EXISTS trg_log_insert_tb_paciente;
DROP TRIGGER IF EXISTS trg_log_update_tb_paciente;
DROP TRIGGER IF EXISTS trg_log_delete_tb_paciente;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_paciente
AFTER INSERT ON tb_paciente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_paciente', 'INSERT', NOW(), NEW.`id_paciente`, NULL, JSON_OBJECT('id_paciente', NEW.`id_paciente`, 'nome_pac', NEW.`nome_pac`, 'nome_social_pac', NEW.`nome_social_pac`, 'endereco_pac', NEW.`endereco_pac`, 'bairro_pac', NEW.`bairro_pac`, 'numero_pac', NEW.`numero_pac`, 'cidade_pac', NEW.`cidade_pac`, 'estado_pac', NEW.`estado_pac`, 'email02_pac', NEW.`email02_pac`, 'data_nasc_pac', NEW.`data_nasc_pac`, 'ativo_pac', NEW.`ativo_pac`, 'telefone01_pac', NEW.`telefone01_pac`, 'telefone02_pac', NEW.`telefone02_pac`, 'email01_pac', NEW.`email01_pac`, 'cpf_pac', NEW.`cpf_pac`, 'data_create_pac', NEW.`data_create_pac`, 'mae_pac', NEW.`mae_pac`, 'fk_estipulante_pac', NEW.`fk_estipulante_pac`, 'cep_pac', NEW.`cep_pac`, 'idade_pac', NEW.`idade_pac`, 'sexo_pac', NEW.`sexo_pac`, 'fk_seguradora_pac', NEW.`fk_seguradora_pac`, 'matricula_pac', NEW.`matricula_pac`, 'obs_pac', NEW.`obs_pac`, 'usuario_create_pac', NEW.`usuario_create_pac`, 'fk_usuario_pac', NEW.`fk_usuario_pac`, 'deletado_pac', NEW.`deletado_pac`, 'complemento_pac', NEW.`complemento_pac`, 'updated_at', NEW.`updated_at`, 'num_atendimento_pac', NEW.`num_atendimento_pac`, 'recem_nascido_pac', NEW.`recem_nascido_pac`, 'matricula_titular_pac', NEW.`matricula_titular_pac`, 'mae_titular_pac', NEW.`mae_titular_pac`, 'numero_rn_pac', NEW.`numero_rn_pac`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_paciente
AFTER UPDATE ON tb_paciente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_paciente', 'UPDATE', NOW(), NEW.`id_paciente`, JSON_OBJECT('id_paciente', OLD.`id_paciente`, 'nome_pac', OLD.`nome_pac`, 'nome_social_pac', OLD.`nome_social_pac`, 'endereco_pac', OLD.`endereco_pac`, 'bairro_pac', OLD.`bairro_pac`, 'numero_pac', OLD.`numero_pac`, 'cidade_pac', OLD.`cidade_pac`, 'estado_pac', OLD.`estado_pac`, 'email02_pac', OLD.`email02_pac`, 'data_nasc_pac', OLD.`data_nasc_pac`, 'ativo_pac', OLD.`ativo_pac`, 'telefone01_pac', OLD.`telefone01_pac`, 'telefone02_pac', OLD.`telefone02_pac`, 'email01_pac', OLD.`email01_pac`, 'cpf_pac', OLD.`cpf_pac`, 'data_create_pac', OLD.`data_create_pac`, 'mae_pac', OLD.`mae_pac`, 'fk_estipulante_pac', OLD.`fk_estipulante_pac`, 'cep_pac', OLD.`cep_pac`, 'idade_pac', OLD.`idade_pac`, 'sexo_pac', OLD.`sexo_pac`, 'fk_seguradora_pac', OLD.`fk_seguradora_pac`, 'matricula_pac', OLD.`matricula_pac`, 'obs_pac', OLD.`obs_pac`, 'usuario_create_pac', OLD.`usuario_create_pac`, 'fk_usuario_pac', OLD.`fk_usuario_pac`, 'deletado_pac', OLD.`deletado_pac`, 'complemento_pac', OLD.`complemento_pac`, 'updated_at', OLD.`updated_at`, 'num_atendimento_pac', OLD.`num_atendimento_pac`, 'recem_nascido_pac', OLD.`recem_nascido_pac`, 'matricula_titular_pac', OLD.`matricula_titular_pac`, 'mae_titular_pac', OLD.`mae_titular_pac`, 'numero_rn_pac', OLD.`numero_rn_pac`), JSON_OBJECT('id_paciente', NEW.`id_paciente`, 'nome_pac', NEW.`nome_pac`, 'nome_social_pac', NEW.`nome_social_pac`, 'endereco_pac', NEW.`endereco_pac`, 'bairro_pac', NEW.`bairro_pac`, 'numero_pac', NEW.`numero_pac`, 'cidade_pac', NEW.`cidade_pac`, 'estado_pac', NEW.`estado_pac`, 'email02_pac', NEW.`email02_pac`, 'data_nasc_pac', NEW.`data_nasc_pac`, 'ativo_pac', NEW.`ativo_pac`, 'telefone01_pac', NEW.`telefone01_pac`, 'telefone02_pac', NEW.`telefone02_pac`, 'email01_pac', NEW.`email01_pac`, 'cpf_pac', NEW.`cpf_pac`, 'data_create_pac', NEW.`data_create_pac`, 'mae_pac', NEW.`mae_pac`, 'fk_estipulante_pac', NEW.`fk_estipulante_pac`, 'cep_pac', NEW.`cep_pac`, 'idade_pac', NEW.`idade_pac`, 'sexo_pac', NEW.`sexo_pac`, 'fk_seguradora_pac', NEW.`fk_seguradora_pac`, 'matricula_pac', NEW.`matricula_pac`, 'obs_pac', NEW.`obs_pac`, 'usuario_create_pac', NEW.`usuario_create_pac`, 'fk_usuario_pac', NEW.`fk_usuario_pac`, 'deletado_pac', NEW.`deletado_pac`, 'complemento_pac', NEW.`complemento_pac`, 'updated_at', NEW.`updated_at`, 'num_atendimento_pac', NEW.`num_atendimento_pac`, 'recem_nascido_pac', NEW.`recem_nascido_pac`, 'matricula_titular_pac', NEW.`matricula_titular_pac`, 'mae_titular_pac', NEW.`mae_titular_pac`, 'numero_rn_pac', NEW.`numero_rn_pac`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_paciente
AFTER DELETE ON tb_paciente
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_paciente', 'DELETE', NOW(), OLD.`id_paciente`, JSON_OBJECT('id_paciente', OLD.`id_paciente`, 'nome_pac', OLD.`nome_pac`, 'nome_social_pac', OLD.`nome_social_pac`, 'endereco_pac', OLD.`endereco_pac`, 'bairro_pac', OLD.`bairro_pac`, 'numero_pac', OLD.`numero_pac`, 'cidade_pac', OLD.`cidade_pac`, 'estado_pac', OLD.`estado_pac`, 'email02_pac', OLD.`email02_pac`, 'data_nasc_pac', OLD.`data_nasc_pac`, 'ativo_pac', OLD.`ativo_pac`, 'telefone01_pac', OLD.`telefone01_pac`, 'telefone02_pac', OLD.`telefone02_pac`, 'email01_pac', OLD.`email01_pac`, 'cpf_pac', OLD.`cpf_pac`, 'data_create_pac', OLD.`data_create_pac`, 'mae_pac', OLD.`mae_pac`, 'fk_estipulante_pac', OLD.`fk_estipulante_pac`, 'cep_pac', OLD.`cep_pac`, 'idade_pac', OLD.`idade_pac`, 'sexo_pac', OLD.`sexo_pac`, 'fk_seguradora_pac', OLD.`fk_seguradora_pac`, 'matricula_pac', OLD.`matricula_pac`, 'obs_pac', OLD.`obs_pac`, 'usuario_create_pac', OLD.`usuario_create_pac`, 'fk_usuario_pac', OLD.`fk_usuario_pac`, 'deletado_pac', OLD.`deletado_pac`, 'complemento_pac', OLD.`complemento_pac`, 'updated_at', OLD.`updated_at`, 'num_atendimento_pac', OLD.`num_atendimento_pac`, 'recem_nascido_pac', OLD.`recem_nascido_pac`, 'matricula_titular_pac', OLD.`matricula_titular_pac`, 'mae_titular_pac', OLD.`mae_titular_pac`, 'numero_rn_pac', OLD.`numero_rn_pac`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_patologia
DROP TRIGGER IF EXISTS trg_log_insert_tb_patologia;
DROP TRIGGER IF EXISTS trg_log_update_tb_patologia;
DROP TRIGGER IF EXISTS trg_log_delete_tb_patologia;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_patologia
AFTER INSERT ON tb_patologia
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_patologia', 'INSERT', NOW(), NEW.`id_patologia`, NULL, JSON_OBJECT('id_patologia', NEW.`id_patologia`, 'patologia_pat', NEW.`patologia_pat`, 'dias_pato', NEW.`dias_pato`, 'fk_usuario_pat', NEW.`fk_usuario_pat`, 'grupo_patologia_pat', NEW.`grupo_patologia_pat`, 'fk_cid_10_pat', NEW.`fk_cid_10_pat`, 'usuario_create_pat', NEW.`usuario_create_pat`, 'data_create_pat', NEW.`data_create_pat`, 'deletado_pat', NEW.`deletado_pat`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_patologia
AFTER UPDATE ON tb_patologia
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_patologia', 'UPDATE', NOW(), NEW.`id_patologia`, JSON_OBJECT('id_patologia', OLD.`id_patologia`, 'patologia_pat', OLD.`patologia_pat`, 'dias_pato', OLD.`dias_pato`, 'fk_usuario_pat', OLD.`fk_usuario_pat`, 'grupo_patologia_pat', OLD.`grupo_patologia_pat`, 'fk_cid_10_pat', OLD.`fk_cid_10_pat`, 'usuario_create_pat', OLD.`usuario_create_pat`, 'data_create_pat', OLD.`data_create_pat`, 'deletado_pat', OLD.`deletado_pat`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_patologia', NEW.`id_patologia`, 'patologia_pat', NEW.`patologia_pat`, 'dias_pato', NEW.`dias_pato`, 'fk_usuario_pat', NEW.`fk_usuario_pat`, 'grupo_patologia_pat', NEW.`grupo_patologia_pat`, 'fk_cid_10_pat', NEW.`fk_cid_10_pat`, 'usuario_create_pat', NEW.`usuario_create_pat`, 'data_create_pat', NEW.`data_create_pat`, 'deletado_pat', NEW.`deletado_pat`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_patologia
AFTER DELETE ON tb_patologia
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_patologia', 'DELETE', NOW(), OLD.`id_patologia`, JSON_OBJECT('id_patologia', OLD.`id_patologia`, 'patologia_pat', OLD.`patologia_pat`, 'dias_pato', OLD.`dias_pato`, 'fk_usuario_pat', OLD.`fk_usuario_pat`, 'grupo_patologia_pat', OLD.`grupo_patologia_pat`, 'fk_cid_10_pat', OLD.`fk_cid_10_pat`, 'usuario_create_pat', OLD.`usuario_create_pat`, 'data_create_pat', OLD.`data_create_pat`, 'deletado_pat', OLD.`deletado_pat`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_prorrogacao
DROP TRIGGER IF EXISTS trg_log_insert_tb_prorrogacao;
DROP TRIGGER IF EXISTS trg_log_update_tb_prorrogacao;
DROP TRIGGER IF EXISTS trg_log_delete_tb_prorrogacao;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_prorrogacao
AFTER INSERT ON tb_prorrogacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_prorrogacao', 'INSERT', NOW(), NEW.`id_prorrogacao`, NULL, JSON_OBJECT('id_prorrogacao', NEW.`id_prorrogacao`, 'acomod1_pror', NEW.`acomod1_pror`, 'isol_1_pror', NEW.`isol_1_pror`, 'fk_internacao_pror', NEW.`fk_internacao_pror`, 'fk_usuario_pror', NEW.`fk_usuario_pror`, 'prorrog1_fim_pror', NEW.`prorrog1_fim_pror`, 'prorrog1_ini_pror', NEW.`prorrog1_ini_pror`, 'fk_visita_pror', NEW.`fk_visita_pror`, 'diarias_1', NEW.`diarias_1`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_prorrogacao
AFTER UPDATE ON tb_prorrogacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_prorrogacao', 'UPDATE', NOW(), NEW.`id_prorrogacao`, JSON_OBJECT('id_prorrogacao', OLD.`id_prorrogacao`, 'acomod1_pror', OLD.`acomod1_pror`, 'isol_1_pror', OLD.`isol_1_pror`, 'fk_internacao_pror', OLD.`fk_internacao_pror`, 'fk_usuario_pror', OLD.`fk_usuario_pror`, 'prorrog1_fim_pror', OLD.`prorrog1_fim_pror`, 'prorrog1_ini_pror', OLD.`prorrog1_ini_pror`, 'fk_visita_pror', OLD.`fk_visita_pror`, 'diarias_1', OLD.`diarias_1`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_prorrogacao', NEW.`id_prorrogacao`, 'acomod1_pror', NEW.`acomod1_pror`, 'isol_1_pror', NEW.`isol_1_pror`, 'fk_internacao_pror', NEW.`fk_internacao_pror`, 'fk_usuario_pror', NEW.`fk_usuario_pror`, 'prorrog1_fim_pror', NEW.`prorrog1_fim_pror`, 'prorrog1_ini_pror', NEW.`prorrog1_ini_pror`, 'fk_visita_pror', NEW.`fk_visita_pror`, 'diarias_1', NEW.`diarias_1`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_prorrogacao
AFTER DELETE ON tb_prorrogacao
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_prorrogacao', 'DELETE', NOW(), OLD.`id_prorrogacao`, JSON_OBJECT('id_prorrogacao', OLD.`id_prorrogacao`, 'acomod1_pror', OLD.`acomod1_pror`, 'isol_1_pror', OLD.`isol_1_pror`, 'fk_internacao_pror', OLD.`fk_internacao_pror`, 'fk_usuario_pror', OLD.`fk_usuario_pror`, 'prorrog1_fim_pror', OLD.`prorrog1_fim_pror`, 'prorrog1_ini_pror', OLD.`prorrog1_ini_pror`, 'fk_visita_pror', OLD.`fk_visita_pror`, 'diarias_1', OLD.`diarias_1`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_seguradora
DROP TRIGGER IF EXISTS trg_log_insert_tb_seguradora;
DROP TRIGGER IF EXISTS trg_log_update_tb_seguradora;
DROP TRIGGER IF EXISTS trg_log_delete_tb_seguradora;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_seguradora
AFTER INSERT ON tb_seguradora
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_seguradora', 'INSERT', NOW(), NEW.`id_seguradora`, NULL, JSON_OBJECT('id_seguradora', NEW.`id_seguradora`, 'seguradora_seg', NEW.`seguradora_seg`, 'endereco_seg', NEW.`endereco_seg`, 'bairro_seg', NEW.`bairro_seg`, 'numero_seg', NEW.`numero_seg`, 'cidade_seg', NEW.`cidade_seg`, 'estado_seg', NEW.`estado_seg`, 'cep_seg', NEW.`cep_seg`, 'coordenador_seg', NEW.`coordenador_seg`, 'coord_rh_seg', NEW.`coord_rh_seg`, 'telefone01_seg', NEW.`telefone01_seg`, 'telefone02_seg', NEW.`telefone02_seg`, 'email01_seg', NEW.`email01_seg`, 'email02_seg', NEW.`email02_seg`, 'data_create_seg', NEW.`data_create_seg`, 'contato_seg', NEW.`contato_seg`, 'logo_seg', NEW.`logo_seg`, 'cnpj_seg', NEW.`cnpj_seg`, 'usuario_create_seg', NEW.`usuario_create_seg`, 'ativo_seg', NEW.`ativo_seg`, 'fk_usuario_seg', NEW.`fk_usuario_seg`, 'deletado_seg', NEW.`deletado_seg`, 'valor_alto_custo_seg', NEW.`valor_alto_custo_seg`, 'dias_visita_seg', NEW.`dias_visita_seg`, 'dias_visita_uti_seg', NEW.`dias_visita_uti_seg`, 'longa_permanencia_seg', NEW.`longa_permanencia_seg`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_seguradora
AFTER UPDATE ON tb_seguradora
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_seguradora', 'UPDATE', NOW(), NEW.`id_seguradora`, JSON_OBJECT('id_seguradora', OLD.`id_seguradora`, 'seguradora_seg', OLD.`seguradora_seg`, 'endereco_seg', OLD.`endereco_seg`, 'bairro_seg', OLD.`bairro_seg`, 'numero_seg', OLD.`numero_seg`, 'cidade_seg', OLD.`cidade_seg`, 'estado_seg', OLD.`estado_seg`, 'cep_seg', OLD.`cep_seg`, 'coordenador_seg', OLD.`coordenador_seg`, 'coord_rh_seg', OLD.`coord_rh_seg`, 'telefone01_seg', OLD.`telefone01_seg`, 'telefone02_seg', OLD.`telefone02_seg`, 'email01_seg', OLD.`email01_seg`, 'email02_seg', OLD.`email02_seg`, 'data_create_seg', OLD.`data_create_seg`, 'contato_seg', OLD.`contato_seg`, 'logo_seg', OLD.`logo_seg`, 'cnpj_seg', OLD.`cnpj_seg`, 'usuario_create_seg', OLD.`usuario_create_seg`, 'ativo_seg', OLD.`ativo_seg`, 'fk_usuario_seg', OLD.`fk_usuario_seg`, 'deletado_seg', OLD.`deletado_seg`, 'valor_alto_custo_seg', OLD.`valor_alto_custo_seg`, 'dias_visita_seg', OLD.`dias_visita_seg`, 'dias_visita_uti_seg', OLD.`dias_visita_uti_seg`, 'longa_permanencia_seg', OLD.`longa_permanencia_seg`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_seguradora', NEW.`id_seguradora`, 'seguradora_seg', NEW.`seguradora_seg`, 'endereco_seg', NEW.`endereco_seg`, 'bairro_seg', NEW.`bairro_seg`, 'numero_seg', NEW.`numero_seg`, 'cidade_seg', NEW.`cidade_seg`, 'estado_seg', NEW.`estado_seg`, 'cep_seg', NEW.`cep_seg`, 'coordenador_seg', NEW.`coordenador_seg`, 'coord_rh_seg', NEW.`coord_rh_seg`, 'telefone01_seg', NEW.`telefone01_seg`, 'telefone02_seg', NEW.`telefone02_seg`, 'email01_seg', NEW.`email01_seg`, 'email02_seg', NEW.`email02_seg`, 'data_create_seg', NEW.`data_create_seg`, 'contato_seg', NEW.`contato_seg`, 'logo_seg', NEW.`logo_seg`, 'cnpj_seg', NEW.`cnpj_seg`, 'usuario_create_seg', NEW.`usuario_create_seg`, 'ativo_seg', NEW.`ativo_seg`, 'fk_usuario_seg', NEW.`fk_usuario_seg`, 'deletado_seg', NEW.`deletado_seg`, 'valor_alto_custo_seg', NEW.`valor_alto_custo_seg`, 'dias_visita_seg', NEW.`dias_visita_seg`, 'dias_visita_uti_seg', NEW.`dias_visita_uti_seg`, 'longa_permanencia_seg', NEW.`longa_permanencia_seg`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_seguradora
AFTER DELETE ON tb_seguradora
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_seguradora', 'DELETE', NOW(), OLD.`id_seguradora`, JSON_OBJECT('id_seguradora', OLD.`id_seguradora`, 'seguradora_seg', OLD.`seguradora_seg`, 'endereco_seg', OLD.`endereco_seg`, 'bairro_seg', OLD.`bairro_seg`, 'numero_seg', OLD.`numero_seg`, 'cidade_seg', OLD.`cidade_seg`, 'estado_seg', OLD.`estado_seg`, 'cep_seg', OLD.`cep_seg`, 'coordenador_seg', OLD.`coordenador_seg`, 'coord_rh_seg', OLD.`coord_rh_seg`, 'telefone01_seg', OLD.`telefone01_seg`, 'telefone02_seg', OLD.`telefone02_seg`, 'email01_seg', OLD.`email01_seg`, 'email02_seg', OLD.`email02_seg`, 'data_create_seg', OLD.`data_create_seg`, 'contato_seg', OLD.`contato_seg`, 'logo_seg', OLD.`logo_seg`, 'cnpj_seg', OLD.`cnpj_seg`, 'usuario_create_seg', OLD.`usuario_create_seg`, 'ativo_seg', OLD.`ativo_seg`, 'fk_usuario_seg', OLD.`fk_usuario_seg`, 'deletado_seg', OLD.`deletado_seg`, 'valor_alto_custo_seg', OLD.`valor_alto_custo_seg`, 'dias_visita_seg', OLD.`dias_visita_seg`, 'dias_visita_uti_seg', OLD.`dias_visita_uti_seg`, 'longa_permanencia_seg', OLD.`longa_permanencia_seg`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_tuss
DROP TRIGGER IF EXISTS trg_log_insert_tb_tuss;
DROP TRIGGER IF EXISTS trg_log_update_tb_tuss;
DROP TRIGGER IF EXISTS trg_log_delete_tb_tuss;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_tuss
AFTER INSERT ON tb_tuss
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss', 'INSERT', NOW(), NEW.`id_tuss`, NULL, JSON_OBJECT('id_tuss', NEW.`id_tuss`, 'fk_int_tuss', NEW.`fk_int_tuss`, 'tuss_solicitado', NEW.`tuss_solicitado`, 'tuss_liberado_sn', NEW.`tuss_liberado_sn`, 'qtd_tuss_solicitado', NEW.`qtd_tuss_solicitado`, 'qtd_tuss_liberado', NEW.`qtd_tuss_liberado`, 'glosa_tuss', NEW.`glosa_tuss`, 'data_realizacao_tuss', NEW.`data_realizacao_tuss`, 'fk_vis_tuss', NEW.`fk_vis_tuss`, 'fk_id_usuario', NEW.`fk_id_usuario`, 'data_create_tuss', NEW.`data_create_tuss`, 'fk_usuario_tuss', NEW.`fk_usuario_tuss`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_tuss
AFTER UPDATE ON tb_tuss
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss', 'UPDATE', NOW(), NEW.`id_tuss`, JSON_OBJECT('id_tuss', OLD.`id_tuss`, 'fk_int_tuss', OLD.`fk_int_tuss`, 'tuss_solicitado', OLD.`tuss_solicitado`, 'tuss_liberado_sn', OLD.`tuss_liberado_sn`, 'qtd_tuss_solicitado', OLD.`qtd_tuss_solicitado`, 'qtd_tuss_liberado', OLD.`qtd_tuss_liberado`, 'glosa_tuss', OLD.`glosa_tuss`, 'data_realizacao_tuss', OLD.`data_realizacao_tuss`, 'fk_vis_tuss', OLD.`fk_vis_tuss`, 'fk_id_usuario', OLD.`fk_id_usuario`, 'data_create_tuss', OLD.`data_create_tuss`, 'fk_usuario_tuss', OLD.`fk_usuario_tuss`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_tuss', NEW.`id_tuss`, 'fk_int_tuss', NEW.`fk_int_tuss`, 'tuss_solicitado', NEW.`tuss_solicitado`, 'tuss_liberado_sn', NEW.`tuss_liberado_sn`, 'qtd_tuss_solicitado', NEW.`qtd_tuss_solicitado`, 'qtd_tuss_liberado', NEW.`qtd_tuss_liberado`, 'glosa_tuss', NEW.`glosa_tuss`, 'data_realizacao_tuss', NEW.`data_realizacao_tuss`, 'fk_vis_tuss', NEW.`fk_vis_tuss`, 'fk_id_usuario', NEW.`fk_id_usuario`, 'data_create_tuss', NEW.`data_create_tuss`, 'fk_usuario_tuss', NEW.`fk_usuario_tuss`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_tuss
AFTER DELETE ON tb_tuss
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss', 'DELETE', NOW(), OLD.`id_tuss`, JSON_OBJECT('id_tuss', OLD.`id_tuss`, 'fk_int_tuss', OLD.`fk_int_tuss`, 'tuss_solicitado', OLD.`tuss_solicitado`, 'tuss_liberado_sn', OLD.`tuss_liberado_sn`, 'qtd_tuss_solicitado', OLD.`qtd_tuss_solicitado`, 'qtd_tuss_liberado', OLD.`qtd_tuss_liberado`, 'glosa_tuss', OLD.`glosa_tuss`, 'data_realizacao_tuss', OLD.`data_realizacao_tuss`, 'fk_vis_tuss', OLD.`fk_vis_tuss`, 'fk_id_usuario', OLD.`fk_id_usuario`, 'data_create_tuss', OLD.`data_create_tuss`, 'fk_usuario_tuss', OLD.`fk_usuario_tuss`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_tuss_ans
DROP TRIGGER IF EXISTS trg_log_insert_tb_tuss_ans;
DROP TRIGGER IF EXISTS trg_log_update_tb_tuss_ans;
DROP TRIGGER IF EXISTS trg_log_delete_tb_tuss_ans;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_tuss_ans
AFTER INSERT ON tb_tuss_ans
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss_ans', 'INSERT', NOW(), NEW.`id_tuss`, NULL, JSON_OBJECT('id_tuss', NEW.`id_tuss`, 'cod_tuss', NEW.`cod_tuss`, 'terminologia_tuss', NEW.`terminologia_tuss`, 'roll_tuss', NEW.`roll_tuss`, 'subgrupo_tuss', NEW.`subgrupo_tuss`, 'grupo_tuss', NEW.`grupo_tuss`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_tuss_ans
AFTER UPDATE ON tb_tuss_ans
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss_ans', 'UPDATE', NOW(), NEW.`id_tuss`, JSON_OBJECT('id_tuss', OLD.`id_tuss`, 'cod_tuss', OLD.`cod_tuss`, 'terminologia_tuss', OLD.`terminologia_tuss`, 'roll_tuss', OLD.`roll_tuss`, 'subgrupo_tuss', OLD.`subgrupo_tuss`, 'grupo_tuss', OLD.`grupo_tuss`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_tuss', NEW.`id_tuss`, 'cod_tuss', NEW.`cod_tuss`, 'terminologia_tuss', NEW.`terminologia_tuss`, 'roll_tuss', NEW.`roll_tuss`, 'subgrupo_tuss', NEW.`subgrupo_tuss`, 'grupo_tuss', NEW.`grupo_tuss`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_tuss_ans
AFTER DELETE ON tb_tuss_ans
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_tuss_ans', 'DELETE', NOW(), OLD.`id_tuss`, JSON_OBJECT('id_tuss', OLD.`id_tuss`, 'cod_tuss', OLD.`cod_tuss`, 'terminologia_tuss', OLD.`terminologia_tuss`, 'roll_tuss', OLD.`roll_tuss`, 'subgrupo_tuss', OLD.`subgrupo_tuss`, 'grupo_tuss', OLD.`grupo_tuss`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_user
DROP TRIGGER IF EXISTS trg_log_insert_tb_user;
DROP TRIGGER IF EXISTS trg_log_update_tb_user;
DROP TRIGGER IF EXISTS trg_log_delete_tb_user;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_user
AFTER INSERT ON tb_user
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user', 'INSERT', NOW(), NEW.`id_usuario`, NULL, JSON_OBJECT('id_usuario', NEW.`id_usuario`, 'usuario_user', NEW.`usuario_user`, 'endereco_user', NEW.`endereco_user`, 'numero_user', NEW.`numero_user`, 'bairro_user', NEW.`bairro_user`, 'cidade_user', NEW.`cidade_user`, 'estado_user', NEW.`estado_user`, 'telefone01_user', NEW.`telefone01_user`, 'telefone02_user', NEW.`telefone02_user`, 'email02_user', NEW.`email02_user`, 'email_user', NEW.`email_user`, 'ativo_user', NEW.`ativo_user`, 'cargo_user', NEW.`cargo_user`, 'nivel_user', NEW.`nivel_user`, 'depto_user', NEW.`depto_user`, 'data_admissao_user', NEW.`data_admissao_user`, 'data_demissao_user', NEW.`data_demissao_user`, 'senha_user', NEW.`senha_user`, 'login_user', NEW.`login_user`, 'senha_default_user', NEW.`senha_default_user`, 'obs_user', NEW.`obs_user`, 'vinculo_user', NEW.`vinculo_user`, 'data_create_user', NEW.`data_create_user`, 'cpf_user', NEW.`cpf_user`, 'reg_profissional_user', NEW.`reg_profissional_user`, 'usuario_create_user', NEW.`usuario_create_user`, 'fk_usuario_user', NEW.`fk_usuario_user`, 'sexo_user', NEW.`sexo_user`, 'idade_user', NEW.`idade_user`, 'tipo_reg_user', NEW.`tipo_reg_user`, 'deletado_user', NEW.`deletado_user`, 'foto_usuario', NEW.`foto_usuario`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_user
AFTER UPDATE ON tb_user
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user', 'UPDATE', NOW(), NEW.`id_usuario`, JSON_OBJECT('id_usuario', OLD.`id_usuario`, 'usuario_user', OLD.`usuario_user`, 'endereco_user', OLD.`endereco_user`, 'numero_user', OLD.`numero_user`, 'bairro_user', OLD.`bairro_user`, 'cidade_user', OLD.`cidade_user`, 'estado_user', OLD.`estado_user`, 'telefone01_user', OLD.`telefone01_user`, 'telefone02_user', OLD.`telefone02_user`, 'email02_user', OLD.`email02_user`, 'email_user', OLD.`email_user`, 'ativo_user', OLD.`ativo_user`, 'cargo_user', OLD.`cargo_user`, 'nivel_user', OLD.`nivel_user`, 'depto_user', OLD.`depto_user`, 'data_admissao_user', OLD.`data_admissao_user`, 'data_demissao_user', OLD.`data_demissao_user`, 'senha_user', OLD.`senha_user`, 'login_user', OLD.`login_user`, 'senha_default_user', OLD.`senha_default_user`, 'obs_user', OLD.`obs_user`, 'vinculo_user', OLD.`vinculo_user`, 'data_create_user', OLD.`data_create_user`, 'cpf_user', OLD.`cpf_user`, 'reg_profissional_user', OLD.`reg_profissional_user`, 'usuario_create_user', OLD.`usuario_create_user`, 'fk_usuario_user', OLD.`fk_usuario_user`, 'sexo_user', OLD.`sexo_user`, 'idade_user', OLD.`idade_user`, 'tipo_reg_user', OLD.`tipo_reg_user`, 'deletado_user', OLD.`deletado_user`, 'foto_usuario', OLD.`foto_usuario`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_usuario', NEW.`id_usuario`, 'usuario_user', NEW.`usuario_user`, 'endereco_user', NEW.`endereco_user`, 'numero_user', NEW.`numero_user`, 'bairro_user', NEW.`bairro_user`, 'cidade_user', NEW.`cidade_user`, 'estado_user', NEW.`estado_user`, 'telefone01_user', NEW.`telefone01_user`, 'telefone02_user', NEW.`telefone02_user`, 'email02_user', NEW.`email02_user`, 'email_user', NEW.`email_user`, 'ativo_user', NEW.`ativo_user`, 'cargo_user', NEW.`cargo_user`, 'nivel_user', NEW.`nivel_user`, 'depto_user', NEW.`depto_user`, 'data_admissao_user', NEW.`data_admissao_user`, 'data_demissao_user', NEW.`data_demissao_user`, 'senha_user', NEW.`senha_user`, 'login_user', NEW.`login_user`, 'senha_default_user', NEW.`senha_default_user`, 'obs_user', NEW.`obs_user`, 'vinculo_user', NEW.`vinculo_user`, 'data_create_user', NEW.`data_create_user`, 'cpf_user', NEW.`cpf_user`, 'reg_profissional_user', NEW.`reg_profissional_user`, 'usuario_create_user', NEW.`usuario_create_user`, 'fk_usuario_user', NEW.`fk_usuario_user`, 'sexo_user', NEW.`sexo_user`, 'idade_user', NEW.`idade_user`, 'tipo_reg_user', NEW.`tipo_reg_user`, 'deletado_user', NEW.`deletado_user`, 'foto_usuario', NEW.`foto_usuario`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_user
AFTER DELETE ON tb_user
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user', 'DELETE', NOW(), OLD.`id_usuario`, JSON_OBJECT('id_usuario', OLD.`id_usuario`, 'usuario_user', OLD.`usuario_user`, 'endereco_user', OLD.`endereco_user`, 'numero_user', OLD.`numero_user`, 'bairro_user', OLD.`bairro_user`, 'cidade_user', OLD.`cidade_user`, 'estado_user', OLD.`estado_user`, 'telefone01_user', OLD.`telefone01_user`, 'telefone02_user', OLD.`telefone02_user`, 'email02_user', OLD.`email02_user`, 'email_user', OLD.`email_user`, 'ativo_user', OLD.`ativo_user`, 'cargo_user', OLD.`cargo_user`, 'nivel_user', OLD.`nivel_user`, 'depto_user', OLD.`depto_user`, 'data_admissao_user', OLD.`data_admissao_user`, 'data_demissao_user', OLD.`data_demissao_user`, 'senha_user', OLD.`senha_user`, 'login_user', OLD.`login_user`, 'senha_default_user', OLD.`senha_default_user`, 'obs_user', OLD.`obs_user`, 'vinculo_user', OLD.`vinculo_user`, 'data_create_user', OLD.`data_create_user`, 'cpf_user', OLD.`cpf_user`, 'reg_profissional_user', OLD.`reg_profissional_user`, 'usuario_create_user', OLD.`usuario_create_user`, 'fk_usuario_user', OLD.`fk_usuario_user`, 'sexo_user', OLD.`sexo_user`, 'idade_user', OLD.`idade_user`, 'tipo_reg_user', OLD.`tipo_reg_user`, 'deletado_user', OLD.`deletado_user`, 'foto_usuario', OLD.`foto_usuario`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_user_permission
DROP TRIGGER IF EXISTS trg_log_insert_tb_user_permission;
DROP TRIGGER IF EXISTS trg_log_update_tb_user_permission;
DROP TRIGGER IF EXISTS trg_log_delete_tb_user_permission;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_user_permission
AFTER INSERT ON tb_user_permission
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user_permission', 'INSERT', NOW(), NEW.`user_id`, NULL, JSON_OBJECT('user_id', NEW.`user_id`, 'can_create', NEW.`can_create`, 'can_edit', NEW.`can_edit`, 'can_delete', NEW.`can_delete`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_user_permission
AFTER UPDATE ON tb_user_permission
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user_permission', 'UPDATE', NOW(), NEW.`user_id`, JSON_OBJECT('user_id', OLD.`user_id`, 'can_create', OLD.`can_create`, 'can_edit', OLD.`can_edit`, 'can_delete', OLD.`can_delete`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('user_id', NEW.`user_id`, 'can_create', NEW.`can_create`, 'can_edit', NEW.`can_edit`, 'can_delete', NEW.`can_delete`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_user_permission
AFTER DELETE ON tb_user_permission
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_user_permission', 'DELETE', NOW(), OLD.`user_id`, JSON_OBJECT('user_id', OLD.`user_id`, 'can_create', OLD.`can_create`, 'can_edit', OLD.`can_edit`, 'can_delete', OLD.`can_delete`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_uti
DROP TRIGGER IF EXISTS trg_log_insert_tb_uti;
DROP TRIGGER IF EXISTS trg_log_update_tb_uti;
DROP TRIGGER IF EXISTS trg_log_delete_tb_uti;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_uti
AFTER INSERT ON tb_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_uti', 'INSERT', NOW(), NEW.`id_uti`, NULL, JSON_OBJECT('id_uti', NEW.`id_uti`, 'fk_internacao_uti', NEW.`fk_internacao_uti`, 'fk_user_uti', NEW.`fk_user_uti`, 'criterios_uti', NEW.`criterios_uti`, 'data_alta_uti', NEW.`data_alta_uti`, 'dva_uti', NEW.`dva_uti`, 'data_internacao_uti', NEW.`data_internacao_uti`, 'hora_internacao_uti', NEW.`hora_internacao_uti`, 'especialidade_uti', NEW.`especialidade_uti`, 'internacao_uti', NEW.`internacao_uti`, 'internado_uti', NEW.`internado_uti`, 'just_uti', NEW.`just_uti`, 'motivo_uti', NEW.`motivo_uti`, 'rel_uti', NEW.`rel_uti`, 'saps_uti', NEW.`saps_uti`, 'score_uti', NEW.`score_uti`, 'vm_uti', NEW.`vm_uti`, 'fk_visita_uti', NEW.`fk_visita_uti`, 'usuario_create_uti', NEW.`usuario_create_uti`, 'glasgow_uti', NEW.`glasgow_uti`, 'suporte_vent_uti', NEW.`suporte_vent_uti`, 'dist_met_uti', NEW.`dist_met_uti`, 'justifique_uti', NEW.`justifique_uti`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_uti
AFTER UPDATE ON tb_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_uti', 'UPDATE', NOW(), NEW.`id_uti`, JSON_OBJECT('id_uti', OLD.`id_uti`, 'fk_internacao_uti', OLD.`fk_internacao_uti`, 'fk_user_uti', OLD.`fk_user_uti`, 'criterios_uti', OLD.`criterios_uti`, 'data_alta_uti', OLD.`data_alta_uti`, 'dva_uti', OLD.`dva_uti`, 'data_internacao_uti', OLD.`data_internacao_uti`, 'hora_internacao_uti', OLD.`hora_internacao_uti`, 'especialidade_uti', OLD.`especialidade_uti`, 'internacao_uti', OLD.`internacao_uti`, 'internado_uti', OLD.`internado_uti`, 'just_uti', OLD.`just_uti`, 'motivo_uti', OLD.`motivo_uti`, 'rel_uti', OLD.`rel_uti`, 'saps_uti', OLD.`saps_uti`, 'score_uti', OLD.`score_uti`, 'vm_uti', OLD.`vm_uti`, 'fk_visita_uti', OLD.`fk_visita_uti`, 'usuario_create_uti', OLD.`usuario_create_uti`, 'glasgow_uti', OLD.`glasgow_uti`, 'suporte_vent_uti', OLD.`suporte_vent_uti`, 'dist_met_uti', OLD.`dist_met_uti`, 'justifique_uti', OLD.`justifique_uti`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_uti', NEW.`id_uti`, 'fk_internacao_uti', NEW.`fk_internacao_uti`, 'fk_user_uti', NEW.`fk_user_uti`, 'criterios_uti', NEW.`criterios_uti`, 'data_alta_uti', NEW.`data_alta_uti`, 'dva_uti', NEW.`dva_uti`, 'data_internacao_uti', NEW.`data_internacao_uti`, 'hora_internacao_uti', NEW.`hora_internacao_uti`, 'especialidade_uti', NEW.`especialidade_uti`, 'internacao_uti', NEW.`internacao_uti`, 'internado_uti', NEW.`internado_uti`, 'just_uti', NEW.`just_uti`, 'motivo_uti', NEW.`motivo_uti`, 'rel_uti', NEW.`rel_uti`, 'saps_uti', NEW.`saps_uti`, 'score_uti', NEW.`score_uti`, 'vm_uti', NEW.`vm_uti`, 'fk_visita_uti', NEW.`fk_visita_uti`, 'usuario_create_uti', NEW.`usuario_create_uti`, 'glasgow_uti', NEW.`glasgow_uti`, 'suporte_vent_uti', NEW.`suporte_vent_uti`, 'dist_met_uti', NEW.`dist_met_uti`, 'justifique_uti', NEW.`justifique_uti`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_uti
AFTER DELETE ON tb_uti
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_uti', 'DELETE', NOW(), OLD.`id_uti`, JSON_OBJECT('id_uti', OLD.`id_uti`, 'fk_internacao_uti', OLD.`fk_internacao_uti`, 'fk_user_uti', OLD.`fk_user_uti`, 'criterios_uti', OLD.`criterios_uti`, 'data_alta_uti', OLD.`data_alta_uti`, 'dva_uti', OLD.`dva_uti`, 'data_internacao_uti', OLD.`data_internacao_uti`, 'hora_internacao_uti', OLD.`hora_internacao_uti`, 'especialidade_uti', OLD.`especialidade_uti`, 'internacao_uti', OLD.`internacao_uti`, 'internado_uti', OLD.`internado_uti`, 'just_uti', OLD.`just_uti`, 'motivo_uti', OLD.`motivo_uti`, 'rel_uti', OLD.`rel_uti`, 'saps_uti', OLD.`saps_uti`, 'score_uti', OLD.`score_uti`, 'vm_uti', OLD.`vm_uti`, 'fk_visita_uti', OLD.`fk_visita_uti`, 'usuario_create_uti', OLD.`usuario_create_uti`, 'glasgow_uti', OLD.`glasgow_uti`, 'suporte_vent_uti', OLD.`suporte_vent_uti`, 'dist_met_uti', OLD.`dist_met_uti`, 'justifique_uti', OLD.`justifique_uti`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

-- Triggers para tb_visita
DROP TRIGGER IF EXISTS trg_log_insert_tb_visita;
DROP TRIGGER IF EXISTS trg_log_update_tb_visita;
DROP TRIGGER IF EXISTS trg_log_delete_tb_visita;
DELIMITER $$
CREATE TRIGGER trg_log_insert_tb_visita
AFTER INSERT ON tb_visita
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_visita', 'INSERT', NOW(), NEW.`id_visita`, NULL, JSON_OBJECT('id_visita', NEW.`id_visita`, 'fk_internacao_vis', NEW.`fk_internacao_vis`, 'rel_visita_vis', NEW.`rel_visita_vis`, 'acoes_int_vis', NEW.`acoes_int_vis`, 'usuario_create', NEW.`usuario_create`, 'visita_no_vis', NEW.`visita_no_vis`, 'visita_auditor_prof_med', NEW.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', NEW.`visita_auditor_prof_enf`, 'visita_med_vis', NEW.`visita_med_vis`, 'visita_enf_vis', NEW.`visita_enf_vis`, 'fk_usuario_vis', NEW.`fk_usuario_vis`, 'data_visita_vis', NEW.`data_visita_vis`, 'data_lancamento_vis', NEW.`data_lancamento_vis`, 'faturado_vis', NEW.`faturado_vis`, 'data_faturamento_vis', NEW.`data_faturamento_vis`, 'oportunidades_enf', NEW.`oportunidades_enf`, 'exames_enf', NEW.`exames_enf`, 'programacao_enf', NEW.`programacao_enf`, 'timer_vis', NEW.`timer_vis`, 'retificou', NEW.`retificou`, 'retificado', NEW.`retificado`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_update_tb_visita
AFTER UPDATE ON tb_visita
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_visita', 'UPDATE', NOW(), NEW.`id_visita`, JSON_OBJECT('id_visita', OLD.`id_visita`, 'fk_internacao_vis', OLD.`fk_internacao_vis`, 'rel_visita_vis', OLD.`rel_visita_vis`, 'acoes_int_vis', OLD.`acoes_int_vis`, 'usuario_create', OLD.`usuario_create`, 'visita_no_vis', OLD.`visita_no_vis`, 'visita_auditor_prof_med', OLD.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', OLD.`visita_auditor_prof_enf`, 'visita_med_vis', OLD.`visita_med_vis`, 'visita_enf_vis', OLD.`visita_enf_vis`, 'fk_usuario_vis', OLD.`fk_usuario_vis`, 'data_visita_vis', OLD.`data_visita_vis`, 'data_lancamento_vis', OLD.`data_lancamento_vis`, 'faturado_vis', OLD.`faturado_vis`, 'data_faturamento_vis', OLD.`data_faturamento_vis`, 'oportunidades_enf', OLD.`oportunidades_enf`, 'exames_enf', OLD.`exames_enf`, 'programacao_enf', OLD.`programacao_enf`, 'timer_vis', OLD.`timer_vis`, 'retificou', OLD.`retificou`, 'retificado', OLD.`retificado`, 'updated_at', OLD.`updated_at`), JSON_OBJECT('id_visita', NEW.`id_visita`, 'fk_internacao_vis', NEW.`fk_internacao_vis`, 'rel_visita_vis', NEW.`rel_visita_vis`, 'acoes_int_vis', NEW.`acoes_int_vis`, 'usuario_create', NEW.`usuario_create`, 'visita_no_vis', NEW.`visita_no_vis`, 'visita_auditor_prof_med', NEW.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', NEW.`visita_auditor_prof_enf`, 'visita_med_vis', NEW.`visita_med_vis`, 'visita_enf_vis', NEW.`visita_enf_vis`, 'fk_usuario_vis', NEW.`fk_usuario_vis`, 'data_visita_vis', NEW.`data_visita_vis`, 'data_lancamento_vis', NEW.`data_lancamento_vis`, 'faturado_vis', NEW.`faturado_vis`, 'data_faturamento_vis', NEW.`data_faturamento_vis`, 'oportunidades_enf', NEW.`oportunidades_enf`, 'exames_enf', NEW.`exames_enf`, 'programacao_enf', NEW.`programacao_enf`, 'timer_vis', NEW.`timer_vis`, 'retificou', NEW.`retificou`, 'retificado', NEW.`retificado`, 'updated_at', NEW.`updated_at`), @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_log_delete_tb_visita
AFTER DELETE ON tb_visita
FOR EACH ROW
BEGIN
  INSERT INTO tb_log_historico
    (tabela, operacao, data_hora, linha_id, estado_antigo, estado_novo, email_user, usuario_id, usuario_nome, ip, user_agent, created_at)
  VALUES
    ('tb_visita', 'DELETE', NOW(), OLD.`id_visita`, JSON_OBJECT('id_visita', OLD.`id_visita`, 'fk_internacao_vis', OLD.`fk_internacao_vis`, 'rel_visita_vis', OLD.`rel_visita_vis`, 'acoes_int_vis', OLD.`acoes_int_vis`, 'usuario_create', OLD.`usuario_create`, 'visita_no_vis', OLD.`visita_no_vis`, 'visita_auditor_prof_med', OLD.`visita_auditor_prof_med`, 'visita_auditor_prof_enf', OLD.`visita_auditor_prof_enf`, 'visita_med_vis', OLD.`visita_med_vis`, 'visita_enf_vis', OLD.`visita_enf_vis`, 'fk_usuario_vis', OLD.`fk_usuario_vis`, 'data_visita_vis', OLD.`data_visita_vis`, 'data_lancamento_vis', OLD.`data_lancamento_vis`, 'faturado_vis', OLD.`faturado_vis`, 'data_faturamento_vis', OLD.`data_faturamento_vis`, 'oportunidades_enf', OLD.`oportunidades_enf`, 'exames_enf', OLD.`exames_enf`, 'programacao_enf', OLD.`programacao_enf`, 'timer_vis', OLD.`timer_vis`, 'retificou', OLD.`retificou`, 'retificado', OLD.`retificado`, 'updated_at', OLD.`updated_at`), NULL, @app_user_email, @app_user_id, @app_user_nome, @app_ip, @app_user_agent, NOW());
END$$
DELIMITER ;

COMMIT;
