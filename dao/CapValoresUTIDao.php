<?php
require_once __DIR__ . '/../models/CapValoresUTI.php';

interface CapValoresUTIDAOInterface
{
    public function buildCapValoresUTI($data);
    public function findAll();
    public function findById($id_uti);
    public function findByCapeante($fk_capeante);
    public function create(CapValoresUTI $uti);
    public function update(CapValoresUTI $utiUpdate);
    public function destroy($id_uti);
    public function findGeral();
    public function findMaxUti();
    public function selectAllCapValoresUTI($where = null, $order = null, $limit = null);
    public function QtdCapValoresUTI($where);
}

class CapValoresUTIDAO implements CapValoresUTIDAOInterface
{
    private $conn;
    private $table = 'tb_cap_valores_uti';
    private $pk    = 'id_uti';

    private const COLS = [
        'fk_capeante',
        'uti_terapias_qtd',
        'uti_terapias_cobrado',
        'uti_terapias_glosado',
        'uti_terapias_obs',
        'uti_taxas_qtd',
        'uti_taxas_cobrado',
        'uti_taxas_glosado',
        'uti_taxas_obs',
        'uti_mat_consumo_qtd',
        'uti_mat_consumo_cobrado',
        'uti_mat_consumo_glosado',
        'uti_mat_consumo_obs',
        'uti_medicametos_qtd',
        'uti_medicametos_cobrado',
        'uti_medicametos_glosado',
        'uti_medicametos_obs',
        'uti_gases_qtd',
        'uti_gases_cobrado',
        'uti_gases_glosado',
        'uti_gases_obs',
        'uti_mat_espec_qtd',
        'uti_mat_espec_cobrado',
        'uti_mat_espec_glosado',
        'uti_mat_espec_obs',
        'uti_exames_qtd',
        'uti_exames_cobrado',
        'uti_exames_glosado',
        'uti_exames_obs',
        'uti_hemoderivados_qtd',
        'uti_hemoderivados_cobrado',
        'uti_hemoderivados_glosado',
        'uti_hemoderivados_obs',
        'uti_honorarios_qtd',
        'uti_honorarios_cobrado',
        'uti_honorarios_glosado',
        'uti_honorarios_obs',
    ];

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function buildCapValoresUTI($data)
    {
        $o = new CapValoresUTI();
        foreach ([$this->pk, 'fk_capeante'] as $k) {
            if (isset($data[$k])) $o->$k = (int)$data[$k];
        }
        foreach (self::COLS as $c) {
            if ($c === 'fk_capeante') continue;
            if (array_key_exists($c, $data)) $o->$c = $data[$c];
        }
        $o->criado_em     = $data['criado_em']    ?? null;
        $o->atualizado_em = $data['atualizado_em'] ?? null;
        return $o;
    }

    public function findAll()
    {
        return $this->conn->query("SELECT * FROM {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id_uti)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = :id LIMIT 1");
        $st->bindValue(':id', (int)$id_uti, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresUTI($row) : null;
    }

    public function findByCapeante($fk_capeante)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE fk_capeante = :fk LIMIT 1");
        $st->bindValue(':fk', (int)$fk_capeante, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresUTI($row) : null;
    }

    public function create(CapValoresUTI $o)
    {
        $cols = [];
        $phs = [];
        $params = [];
        foreach (self::COLS as $c) {
            $cols[] = $c;
            $phs[] = ":$c";
            $params[":$c"] = $o->$c ?? null;
        }
        $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $phs) . ")";
        $st = $this->conn->prepare($sql);
        $st->execute($params);
        return (int)$this->conn->lastInsertId();
    }

    public function update(CapValoresUTI $o)
    {
        if (empty($o->id_uti)) return 0;
        $sets = [];
        $params = [':id' => (int)$o->id_uti];
        foreach (self::COLS as $c) {
            $sets[] = "$c = :$c";
            $params[":$c"] = $o->$c ?? null;
        }
        $sql = "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE {$this->pk}=:id";
        $st = $this->conn->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public function destroy($id_uti)
    {
        $st = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->pk}=:id");
        $st->bindValue(':id', (int)$id_uti, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount();
    }

    public function findGeral()
    {
        return $this->conn->query("SELECT * FROM {$this->table} ORDER BY {$this->pk} DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findMaxUti()
    {
        return (int)($this->conn->query("SELECT MAX({$this->pk}) FROM {$this->table}")->fetchColumn() ?: 0);
    }

    public function selectAllCapValoresUTI($where = null, $order = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE $where";
        if ($order) $sql .= " ORDER BY $order";
        if ($limit) $sql .= " LIMIT $limit";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function QtdCapValoresUTI($where)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE $where";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}