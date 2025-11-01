<?php
require_once __DIR__ . '/../models/CapValoresAP.php';

interface CapValoresAPDAOInterface
{
    public function buildCapValoresAP($data);
    public function findAll();
    public function findById($id_ap);
    public function findByCapeante($fk_capeante);
    public function create(CapValoresAP $ap);
    public function update(CapValoresAP $apUpdate);
    public function destroy($id_ap);
    public function findGeral(); // opcional: join com capeante se quiser
    public function findMaxAp();
    public function selectAllCapValoresAP($where = null, $order = null, $limit = null);
    public function QtdCapValoresAP($where);
}

class CapValoresAPDAO implements CapValoresAPDAOInterface
{
    private $conn;
    private $table = 'tb_cap_valores_ap';
    private $pk    = 'id_ap';

    // lista das colunas graváveis (todas VARCHAR(20) na base)
    private const COLS = [
        'fk_capeante',
        'ap_terapias_qtd',
        'ap_terapias_cobrado',
        'ap_terapias_glosado',
        'ap_terapias_obs',
        'ap_taxas_qtd',
        'ap_taxas_cobrado',
        'ap_taxas_glosado',
        'ap_taxas_obs',
        'ap_mat_consumo_qtd',
        'ap_mat_consumo_cobrado',
        'ap_mat_consumo_glosado',
        'ap_mat_consumo_obs',
        'ap_medicametos_qtd',
        'ap_medicametos_cobrado',
        'ap_medicametos_glosado',
        'ap_medicametos_obs',
        'ap_gases_qtd',
        'ap_gases_cobrado',
        'ap_gases_glosado',
        'ap_gases_obs',
        'ap_mat_espec_qtd',
        'ap_mat_espec_cobrado',
        'ap_mat_espec_glosado',
        'ap_mat_espec_obs',
        'ap_exames_qtd',
        'ap_exames_cobrado',
        'ap_exames_glosado',
        'ap_exames_obs',
        'ap_hemoderivados_qtd',
        'ap_hemoderivados_cobrado',
        'ap_hemoderivados_glosado',
        'ap_hemoderivados_obs',
        'ap_honorarios_qtd',
        'ap_honorarios_cobrado',
        'ap_honorarios_glosado',
        'ap_honorarios_obs',
    ];

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function buildCapValoresAP($data)
    {
        $o = new CapValoresAP();
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
        $sql = "SELECT * FROM {$this->table}";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id_ap)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = :id LIMIT 1");
        $st->bindValue(':id', (int)$id_ap, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresAP($row) : null;
    }

    public function findByCapeante($fk_capeante)
    {
        $st = $this->conn->prepare("SELECT * FROM {$this->table} WHERE fk_capeante = :fk LIMIT 1");
        $st->bindValue(':fk', (int)$fk_capeante, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->buildCapValoresAP($row) : null;
    }

    public function create(CapValoresAP $ap)
    {
        $cols = [];
        $phs  = [];
        $params = [];
        foreach (self::COLS as $c) {
            $cols[] = $c;
            $phs[]  = ':' . $c;
            $params[':' . $c] = $ap->$c ?? null;
        }
        $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ")
            VALUES (" . implode(',', $phs) . ")";
        $st = $this->conn->prepare($sql);
        $st->execute($params);
        return (int)$this->conn->lastInsertId();
    }

    public function update(CapValoresAP $apUpdate)
    {
        if (empty($apUpdate->id_ap)) return 0;
        $sets = [];
        $params = [':id' => (int)$apUpdate->id_ap];
        foreach (self::COLS as $c) {
            if ($c === 'fk_capeante' && !isset($apUpdate->$c)) continue; // opcional
            $sets[] = "{$c} = :{$c}";
            $params[':' . $c] = $apUpdate->$c ?? null;
        }
        if (!$sets) return 0;
        $sql = "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE {$this->pk} = :id";
        $st = $this->conn->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public function destroy($id_ap)
    {
        $st = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->pk} = :id");
        $st->bindValue(':id', (int)$id_ap, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount();
    }

    public function findGeral()
    {
        // ajuste se quiser JOIN com tb_capeante
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->pk} DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findMaxAp()
    {
        $sql = "SELECT MAX({$this->pk}) AS max_id FROM {$this->table}";
        return (int)($this->conn->query($sql)->fetchColumn() ?: 0);
    }

    public function selectAllCapValoresAP($where = null, $order = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        if ($limit) $sql .= " LIMIT {$limit}";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function QtdCapValoresAP($where)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}