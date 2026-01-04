<?php

require_once("models/solicitacao_customizacao.php");
require_once("models/message.php");

class SolicitacaoCustomizacaoDAO implements SolicitacaoCustomizacaoDAOInterface
{
    private $conn;
    private $url;
    private $message;
    private $solicitacaoColumns;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
        $this->solicitacaoColumns = $this->fetchColumns('tb_solicitacao_customizacao');
    }

    public function create(SolicitacaoCustomizacao $solicitacao, array $modulos, array $tipos, array $anexos)
    {
        $now = date('Y-m-d H:i:s');
        $insertData = [
            'fk_usuario_solicitante' => $solicitacao->fk_usuario_solicitante ?: null,
            'nome' => $solicitacao->nome,
            'empresa' => $solicitacao->empresa,
            'cargo' => $solicitacao->cargo,
            'email' => $solicitacao->email,
            'telefone' => $solicitacao->telefone,
            'data_solicitacao' => $solicitacao->data_solicitacao,
            'modulo_outro' => $solicitacao->modulo_outro,
            'descricao' => $solicitacao->descricao,
            'problema_atual' => $solicitacao->problema_atual,
            'resultado_esperado' => $solicitacao->resultado_esperado,
            'impacto_nivel' => $solicitacao->impacto_nivel,
            'descricao_impacto' => $solicitacao->descricao_impacto,
            'prioridade' => $solicitacao->prioridade,
            'prazo_desejado' => $solicitacao->prazo_desejado,
            'responsavel' => $solicitacao->responsavel,
            'assinatura' => $solicitacao->assinatura,
            'data_aprovacao' => $solicitacao->data_aprovacao,
            'prazo_resposta' => $solicitacao->prazo_resposta,
            'precificacao' => $solicitacao->precificacao,
            'observacoes_resposta' => $solicitacao->observacoes_resposta,
            'aprovacao_resposta' => $solicitacao->aprovacao_resposta,
            'data_resposta' => $solicitacao->data_resposta,
            'aprovacao_conex' => $solicitacao->aprovacao_conex,
            'data_aprovacao_conex' => $solicitacao->data_aprovacao_conex,
            'responsavel_aprovacao_conex' => $solicitacao->responsavel_aprovacao_conex,
            'status' => $solicitacao->status,
            'resolvido_em' => $solicitacao->resolvido_em,
            'resolvido_por' => $solicitacao->resolvido_por ?: null,
            'versao_sistema' => $solicitacao->versao_sistema,
            'criado_em' => $now,
            'atualizado_em' => $now,
        ];

        $filtered = $this->filterColumns($insertData, $this->solicitacaoColumns);
        $columns = array_keys($filtered);
        $placeholders = array_map(function ($col) {
            return ':' . $col;
        }, $columns);

        $stmt = $this->conn->prepare("INSERT INTO tb_solicitacao_customizacao (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")");
        $this->bindValues($stmt, $filtered);
        $stmt->execute();
        $id = (int)$this->conn->lastInsertId();

        $this->syncModulos($id, $modulos);
        $this->syncTipos($id, $tipos);
        $this->createAnexos($id, $anexos);

        return $id;
    }

    public function update(SolicitacaoCustomizacao $solicitacao, array $modulos, array $tipos, array $anexos, array $removerAnexos)
    {
        $now = date('Y-m-d H:i:s');
        $updateData = [
            'fk_usuario_solicitante' => $solicitacao->fk_usuario_solicitante ?: null,
            'nome' => $solicitacao->nome,
            'empresa' => $solicitacao->empresa,
            'cargo' => $solicitacao->cargo,
            'email' => $solicitacao->email,
            'telefone' => $solicitacao->telefone,
            'data_solicitacao' => $solicitacao->data_solicitacao,
            'modulo_outro' => $solicitacao->modulo_outro,
            'descricao' => $solicitacao->descricao,
            'problema_atual' => $solicitacao->problema_atual,
            'resultado_esperado' => $solicitacao->resultado_esperado,
            'impacto_nivel' => $solicitacao->impacto_nivel,
            'descricao_impacto' => $solicitacao->descricao_impacto,
            'prioridade' => $solicitacao->prioridade,
            'prazo_desejado' => $solicitacao->prazo_desejado,
            'responsavel' => $solicitacao->responsavel,
            'assinatura' => $solicitacao->assinatura,
            'data_aprovacao' => $solicitacao->data_aprovacao,
            'prazo_resposta' => $solicitacao->prazo_resposta,
            'precificacao' => $solicitacao->precificacao,
            'observacoes_resposta' => $solicitacao->observacoes_resposta,
            'aprovacao_resposta' => $solicitacao->aprovacao_resposta,
            'data_resposta' => $solicitacao->data_resposta,
            'aprovacao_conex' => $solicitacao->aprovacao_conex,
            'data_aprovacao_conex' => $solicitacao->data_aprovacao_conex,
            'responsavel_aprovacao_conex' => $solicitacao->responsavel_aprovacao_conex,
            'status' => $solicitacao->status,
            'resolvido_em' => $solicitacao->resolvido_em,
            'resolvido_por' => $solicitacao->resolvido_por ?: null,
            'versao_sistema' => $solicitacao->versao_sistema,
            'atualizado_em' => $now,
        ];

        $filtered = $this->filterColumns($updateData, $this->solicitacaoColumns);
        $setParts = [];
        foreach (array_keys($filtered) as $col) {
            $setParts[] = $col . ' = :' . $col;
        }
        $stmt = $this->conn->prepare("UPDATE tb_solicitacao_customizacao SET " . implode(', ', $setParts) . " WHERE id_solicitacao = :id_solicitacao");
        $this->bindValues($stmt, $filtered);
        $stmt->bindValue(':id_solicitacao', $solicitacao->id_solicitacao, PDO::PARAM_INT);
        $stmt->execute();

        $id = (int)$solicitacao->id_solicitacao;
        $this->syncModulos($id, $modulos);
        $this->syncTipos($id, $tipos);
        $this->createAnexos($id, $anexos);
        $this->removeAnexos($removerAnexos);

        return $id;
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare("SELECT
                s.*,
                (SELECT GROUP_CONCAT(modulo ORDER BY id_modulo SEPARATOR ', ')
                 FROM tb_solicitacao_customizacao_modulo m
                 WHERE m.fk_solicitacao = s.id_solicitacao) AS modulos,
                (SELECT GROUP_CONCAT(tipo ORDER BY id_tipo SEPARATOR ', ')
                 FROM tb_solicitacao_customizacao_tipo t
                 WHERE t.fk_solicitacao = s.id_solicitacao) AS tipos
            FROM tb_solicitacao_customizacao s
            ORDER BY s.id_solicitacao DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id_solicitacao)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_solicitacao_customizacao WHERE id_solicitacao = :id");
        $stmt->bindValue(":id", $id_solicitacao, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return $this->buildSolicitacao($data);
    }

    public function findAnexos($id_solicitacao)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tb_solicitacao_customizacao_anexo WHERE fk_solicitacao = :id ORDER BY id_anexo DESC");
        $stmt->bindValue(":id", $id_solicitacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findModulos($id_solicitacao)
    {
        $stmt = $this->conn->prepare("SELECT modulo FROM tb_solicitacao_customizacao_modulo WHERE fk_solicitacao = :id");
        $stmt->bindValue(":id", $id_solicitacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findTipos($id_solicitacao)
    {
        $stmt = $this->conn->prepare("SELECT tipo FROM tb_solicitacao_customizacao_tipo WHERE fk_solicitacao = :id");
        $stmt->bindValue(":id", $id_solicitacao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function buildSolicitacao(array $data)
    {
        $s = new SolicitacaoCustomizacao();
        $s->id_solicitacao = $data['id_solicitacao'];
        $s->fk_usuario_solicitante = $data['fk_usuario_solicitante'];
        $s->nome = $data['nome'];
        $s->empresa = $data['empresa'];
        $s->cargo = $data['cargo'];
        $s->email = $data['email'];
        $s->telefone = $data['telefone'];
        $s->data_solicitacao = $data['data_solicitacao'];
        $s->modulo_outro = $data['modulo_outro'];
        $s->descricao = $data['descricao'];
        $s->problema_atual = $data['problema_atual'];
        $s->resultado_esperado = $data['resultado_esperado'];
        $s->impacto_nivel = $data['impacto_nivel'];
        $s->descricao_impacto = $data['descricao_impacto'];
        $s->prioridade = $data['prioridade'];
        $s->prazo_desejado = $data['prazo_desejado'];
        $s->responsavel = $data['responsavel'];
        $s->assinatura = $data['assinatura'];
        $s->data_aprovacao = $data['data_aprovacao'];
        $s->prazo_resposta = $data['prazo_resposta'];
        $s->precificacao = $data['precificacao'];
        $s->observacoes_resposta = $data['observacoes_resposta'];
        $s->aprovacao_resposta = $data['aprovacao_resposta'];
        $s->data_resposta = $data['data_resposta'];
        $s->aprovacao_conex = $data['aprovacao_conex'] ?? null;
        $s->data_aprovacao_conex = $data['data_aprovacao_conex'] ?? null;
        $s->responsavel_aprovacao_conex = $data['responsavel_aprovacao_conex'] ?? null;
        $s->status = $data['status'];
        $s->resolvido_em = $data['resolvido_em'];
        $s->resolvido_por = $data['resolvido_por'];
        $s->versao_sistema = $data['versao_sistema'];
        $s->criado_em = $data['criado_em'];
        $s->atualizado_em = $data['atualizado_em'];
        return $s;
    }

    private function syncModulos(int $idSolicitacao, array $modulos)
    {
        $this->conn->prepare("DELETE FROM tb_solicitacao_customizacao_modulo WHERE fk_solicitacao = :id")
            ->execute([":id" => $idSolicitacao]);

        if (!$modulos) {
            return;
        }

        $columns = $this->fetchColumns('tb_solicitacao_customizacao_modulo');
        $hasCriadoEm = isset($columns['criado_em']);
        $sql = $hasCriadoEm
            ? "INSERT INTO tb_solicitacao_customizacao_modulo (fk_solicitacao, modulo, criado_em) VALUES (:id, :modulo, NOW())"
            : "INSERT INTO tb_solicitacao_customizacao_modulo (fk_solicitacao, modulo) VALUES (:id, :modulo)";
        $stmt = $this->conn->prepare($sql);
        foreach ($modulos as $modulo) {
            $stmt->execute([
                ":id" => $idSolicitacao,
                ":modulo" => $modulo,
            ]);
        }
    }

    private function syncTipos(int $idSolicitacao, array $tipos)
    {
        $this->conn->prepare("DELETE FROM tb_solicitacao_customizacao_tipo WHERE fk_solicitacao = :id")
            ->execute([":id" => $idSolicitacao]);

        if (!$tipos) {
            return;
        }

        $columns = $this->fetchColumns('tb_solicitacao_customizacao_tipo');
        $hasCriadoEm = isset($columns['criado_em']);
        $sql = $hasCriadoEm
            ? "INSERT INTO tb_solicitacao_customizacao_tipo (fk_solicitacao, tipo, criado_em) VALUES (:id, :tipo, NOW())"
            : "INSERT INTO tb_solicitacao_customizacao_tipo (fk_solicitacao, tipo) VALUES (:id, :tipo)";
        $stmt = $this->conn->prepare($sql);
        foreach ($tipos as $tipo) {
            $stmt->execute([
                ":id" => $idSolicitacao,
                ":tipo" => $tipo,
            ]);
        }
    }

    private function createAnexos(int $idSolicitacao, array $anexos)
    {
        if (!$anexos) {
            return;
        }

        $columns = $this->fetchColumns('tb_solicitacao_customizacao_anexo');
        $hasCriadoEm = isset($columns['criado_em']);
        $stmt = $this->conn->prepare($hasCriadoEm
            ? "INSERT INTO tb_solicitacao_customizacao_anexo (
                fk_solicitacao,
                caminho_arquivo,
                nome_original,
                mime,
                tamanho,
                criado_em
            ) VALUES (
                :id,
                :caminho,
                :nome,
                :mime,
                :tamanho,
                NOW()
            )"
            : "INSERT INTO tb_solicitacao_customizacao_anexo (
                fk_solicitacao,
                caminho_arquivo,
                nome_original,
                mime,
                tamanho
            ) VALUES (
                :id,
                :caminho,
                :nome,
                :mime,
                :tamanho
            )"
        );

        foreach ($anexos as $anexo) {
            $stmt->execute([
                ":id" => $idSolicitacao,
                ":caminho" => $anexo['caminho_arquivo'],
                ":nome" => $anexo['nome_original'],
                ":mime" => $anexo['mime'],
                ":tamanho" => $anexo['tamanho'],
            ]);
        }
    }

    private function removeAnexos(array $removerIds)
    {
        if (!$removerIds) {
            return;
        }

        $stmt = $this->conn->prepare("SELECT id_anexo, caminho_arquivo FROM tb_solicitacao_customizacao_anexo WHERE id_anexo = :id");
        $del = $this->conn->prepare("DELETE FROM tb_solicitacao_customizacao_anexo WHERE id_anexo = :id");
        foreach ($removerIds as $id) {
            $stmt->execute([":id" => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['caminho_arquivo']) && is_file($row['caminho_arquivo'])) {
                @unlink($row['caminho_arquivo']);
            }
            $del->execute([":id" => $id]);
        }
    }

    private function fetchColumns(string $table): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
            $stmt->bindValue(':table', $table);
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_fill_keys($cols ?: [], true);
        } catch (Exception $e) {
            return [];
        }
    }

    private function filterColumns(array $data, array $columns): array
    {
        if (!$columns) {
            return $data;
        }
        $filtered = [];
        foreach ($data as $col => $value) {
            if (isset($columns[$col])) {
                $filtered[$col] = $value;
            }
        }
        return $filtered;
    }

    private function bindValues(PDOStatement $stmt, array $data): void
    {
        foreach ($data as $col => $value) {
            if ($value === null || $value === '') {
                $stmt->bindValue(':' . $col, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':' . $col, $value);
            }
        }
    }
}
