<?php
require_once __DIR__ . '/../models/CapValoresCC.php';

interface CapValoresCCDAOInterface
{
    public function buildCapValoresCC($data);
    public function findAll();
    public function findById($id_cc);
    public function findByCapeante($fk_capeante);
    public function create(CapValoresCC $cc);
    public function update(CapValoresCC $ccUpdate);
    public function destroy($id_cc);
    public function findGeral();
    public function findMaxCc();
    public function selectAllCapValoresCC($where = null, $order = null, $limit = null);
    public function QtdCapValoresCC($where);
}

class CapValoresCCDAO implements CapValoresCCDAOInterface
{
    private $conn;
    private $table = 'tb_cap_valores_cc';
    private $pk    = 'id_cc';

    private const COLS = [
        'fk_capeante',
        'cc_terapias_qtd',
        'cc_terapias_cobrado',
        'cc_terapias_glosado',
        'cc_terapias_obs',
        'cc_taxas_qtd',
        'cc_taxas_cobrado',
        'cc_taxas_glosado',
        'cc_taxas_obs',
        'cc_mat_consumo_qtd',
        'cc_mat_consumo_cobrado',
        'cc_mat_consumo_glosado',
        'cc_mat_consumo_obs',
        'cc_medicametos_qtd',
        'cc_medicametos_cobrado',
        'cc_medicametos_glosado',
        'cc_medicametos_obs',
        'cc_gases_qtd',
        'cc_gases_cobrado',
        'cc_gases_glosado',
        'cc_gases_obs',
        'cc_mat_espec_qtd',
        'cc_mat_espec_cobrado',
        'cc_mat_espec_glosado',
        'cc_mat_espec_obs',
        'cc_exames_qtd',
        'cc_exames_cobrado',
        'cc_exames_glosado',
        'cc_exames_obs',
        'cc_hemoderivados_qtd',
        'cc_hemoderivados_cobrado',
        'cc_hemoderivados_glosado',
        'cc_hemoderivados_obs',
        'cc_honorarios_qtd',
        'cc_honorarios_cobrado',
        'cc_honorarios_glosado',
        'cc_honorarios_obs',
    ];

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function buildCapValoresCC($data)
    {
        $o = new CapValoresCC();
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

    public function findById($id_cc)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = :id LIMIT 1");
        $st->bindValue(':id', (int)$id_cc, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresCC($row) : null;
    }

    public function findByCapeante($fk_capeante)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE fk_capeante = :fk LIMIT 1");
        $st->bindValue(':fk', (int)$fk_capeante, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresCC($row) : null;
    }

    public function create(CapValoresCC $o)
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

    public function update(CapValoresCC $o)
    {
        if (empty($o->id_cc)) return 0;
        $sets = [];
        $params = [':id' => (int)$o->id_cc];
        foreach (self::COLS as $c) {
            $sets[] = "$c=:$c";
            $params[":$c"] = $o->$c ?? null;
        }
        $sql = "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE {$this->pk}=:id";
        $st = $this->conn->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public function destroy($id_cc)
    {
        $st = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->pk}=:id");
        $st->bindValue(':id', (int)$id_cc, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount();
    }

    public function findGeral()
    {
        return $this->conn->query("SELECT * FROM {$this->table} ORDER BY {$this->pk} DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findMaxCc()
    {
        return (int)($this->conn->query("SELECT MAX({$this->pk}) FROM {$this->table}")->fetchColumn() ?: 0);
    }

    public function selectAllCapValoresCC($where = null, $order = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE $where";
        if ($order) $sql .= " ORDER BY $order";
        if ($limit) $sql .= " LIMIT $limit";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function QtdCapValoresCC($where)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE $where";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}