<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/permission.php';

final class PermissionDAO
{
    /** @var PDO */
    private $conn;
    /** @var string */
    private $baseUrl;

    // nomes de tabela/colunas centralizados
    private const T_USERS      = 'tb_user';
    private const COL_UID      = 'id_usuario';
    private const COL_NAME     = 'usuario_user';
    private const COL_EMAIL    = 'email_user';
    private const COL_CARGO    = 'cargo_user';

    private const T_PERMS      = 'tb_user_permission';
    private const COL_P_UID    = 'user_id';
    private const COL_VIEW     = 'can_view';
    private const COL_CREATE   = 'can_create';
    private const COL_EDIT     = 'can_edit';
    private const COL_DELETE   = 'can_delete';
    private const COL_DISCHARGE = 'can_discharge';
    private const COL_CLOSE_MANAGEMENT = 'can_close_management';
    private const COL_GENERATE_PDF = 'can_generate_pdf';
    private const COL_UPDATED  = 'updated_at';
    private static bool $schemaEnsured = false;

    public function __construct(PDO $conn, string $baseUrl)
    {
        $this->conn    = $conn;
        $this->baseUrl = $baseUrl;

        // PDO em modo seguro (caso não esteja no bootstrap)
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if (!self::$schemaEnsured) {
            $this->ensurePermissionColumns();
            self::$schemaEnsured = true;
        }
    }

    /* ========== READS ========== */

    /** Retorna matriz p/ a tela (todos os usuários + permissões se houver) */
    public function findAllWithUsers(): array
    {
        $sql = "
            SELECT 
                u." . self::COL_UID . "      AS id_user,
                u." . self::COL_NAME . "     AS nome,
                u." . self::COL_EMAIL . "    AS email,
                u." . self::COL_CARGO . "    AS cargo,
                IFNULL(p." . self::COL_VIEW . ",   1) AS " . self::COL_VIEW . ",
                IFNULL(p." . self::COL_CREATE . ", 0) AS " . self::COL_CREATE . ",
                IFNULL(p." . self::COL_EDIT . ",   0) AS " . self::COL_EDIT . ",
                IFNULL(p." . self::COL_DELETE . ", 0) AS " . self::COL_DELETE . ",
                IFNULL(p." . self::COL_DISCHARGE . ", 0) AS " . self::COL_DISCHARGE . ",
                IFNULL(p." . self::COL_CLOSE_MANAGEMENT . ", 0) AS " . self::COL_CLOSE_MANAGEMENT . ",
                IFNULL(p." . self::COL_GENERATE_PDF . ", 0) AS " . self::COL_GENERATE_PDF . ",
                p." . self::COL_UPDATED . "        AS " . self::COL_UPDATED . "
            FROM " . self::T_USERS . " u
            LEFT JOIN " . self::T_PERMS . " p
              ON p." . self::COL_P_UID . " = u." . self::COL_UID . "
            ORDER BY u." . self::COL_NAME . " ASC
        ";
        $st = $this->conn->query($sql);
        return $st->fetchAll();
    }

    /** Retorna Permission de 1 usuário (ou zeros se não houver linha) */
    public function getByUser(int $userId): Permission
    {
        $sql = "SELECT " . self::COL_VIEW . ", " . self::COL_CREATE . ", " . self::COL_EDIT . ", " . self::COL_DELETE . ",
                       " . self::COL_DISCHARGE . ", " . self::COL_CLOSE_MANAGEMENT . ", " . self::COL_GENERATE_PDF . ",
                       " . self::COL_UPDATED . "
                FROM " . self::T_PERMS . "
                WHERE " . self::COL_P_UID . " = :uid";
        $st  = $this->conn->prepare($sql);
        $st->execute([':uid' => $userId]);
        $row = $st->fetch();

        $p = new Permission();
        $p->user_id    = $userId;
        $p->can_view   = isset($row[self::COL_VIEW])   ? (int)$row[self::COL_VIEW]   : 1;
        $p->can_create = isset($row[self::COL_CREATE]) ? (int)$row[self::COL_CREATE] : 0;
        $p->can_edit   = isset($row[self::COL_EDIT])   ? (int)$row[self::COL_EDIT]   : 0;
        $p->can_delete = isset($row[self::COL_DELETE]) ? (int)$row[self::COL_DELETE] : 0;
        $p->can_discharge = isset($row[self::COL_DISCHARGE]) ? (int)$row[self::COL_DISCHARGE] : 0;
        $p->can_close_management = isset($row[self::COL_CLOSE_MANAGEMENT]) ? (int)$row[self::COL_CLOSE_MANAGEMENT] : 0;
        $p->can_generate_pdf = isset($row[self::COL_GENERATE_PDF]) ? (int)$row[self::COL_GENERATE_PDF] : 0;
        $p->updated_at = $row[self::COL_UPDATED] ?? null;
        return $p;
    }

    /* ========== WRITES ========== */

    /** Seta permissões para 1 usuário (idempotente) */
    public function setUserPerms(int $userId, bool $create, bool $edit, bool $delete): void
    {
        $sql = "INSERT INTO " . self::T_PERMS . "
                    (" . self::COL_P_UID . ", " . self::COL_CREATE . ", " . self::COL_EDIT . ", " . self::COL_DELETE . ")
                VALUES (:uid, :c, :e, :d)
                ON DUPLICATE KEY UPDATE
                    " . self::COL_CREATE . " = VALUES(" . self::COL_CREATE . "),
                    " . self::COL_EDIT . "   = VALUES(" . self::COL_EDIT . "),
                    " . self::COL_DELETE . " = VALUES(" . self::COL_DELETE . ")";
        $st = $this->conn->prepare($sql);
        $st->execute([
            ':uid' => $userId,
            ':c'   => $create ? 1 : 0,
            ':e'   => $edit   ? 1 : 0,
            ':d'   => $delete ? 1 : 0,
        ]);
    }

    /**
     * Atualiza em lote a matriz de permissões.
     * Exemplo de estrutura (comentário, não código executável):
     *   $permMatrix = array(
     *     10 => array('create' => '1', 'edit' => '0', 'delete' => '1'),
     *     22 => array('create' => '0', 'edit' => '1', 'delete' => '0'),
     *   );
     */
    public function bulkUpdate(array $permMatrix): void
    {
        if (empty($permMatrix)) {
            return;
        }

        // 1) Seleciona IDs válidos na tb_user
        $ids   = array_map('intval', array_keys($permMatrix));
        $valid = $this->filterValidUserIds($ids);
        if (!$valid) {
            return;
        }

        // 2) Upsert em transação
        $this->conn->beginTransaction();
        try {
            $sql = "INSERT INTO " . self::T_PERMS . "
                        (" . self::COL_P_UID . ", " . self::COL_VIEW . ", " . self::COL_CREATE . ", " . self::COL_EDIT . ", " . self::COL_DELETE . ",
                         " . self::COL_DISCHARGE . ", " . self::COL_CLOSE_MANAGEMENT . ", " . self::COL_GENERATE_PDF . ")
                    VALUES (:uid,:v,:c,:e,:d,:dis,:cm,:pdf)
                    ON DUPLICATE KEY UPDATE
                        " . self::COL_VIEW . "   = VALUES(" . self::COL_VIEW . "),
                        " . self::COL_CREATE . " = VALUES(" . self::COL_CREATE . "),
                        " . self::COL_EDIT . "   = VALUES(" . self::COL_EDIT . "),
                        " . self::COL_DELETE . " = VALUES(" . self::COL_DELETE . "),
                        " . self::COL_DISCHARGE . " = VALUES(" . self::COL_DISCHARGE . "),
                        " . self::COL_CLOSE_MANAGEMENT . " = VALUES(" . self::COL_CLOSE_MANAGEMENT . "),
                        " . self::COL_GENERATE_PDF . " = VALUES(" . self::COL_GENERATE_PDF . ")";
            $up = $this->conn->prepare($sql);

            foreach ($valid as $uid) {
                $flags = isset($permMatrix[$uid]) && is_array($permMatrix[$uid]) ? $permMatrix[$uid] : array();
                $v = !empty($flags['view'])         && (string)$flags['view']         === '1';
                $c = !empty($flags['create']) && (string)$flags['create'] === '1';
                $e = !empty($flags['edit'])   && (string)$flags['edit']   === '1';
                $d = !empty($flags['delete']) && (string)$flags['delete'] === '1';
                $dis = !empty($flags['discharge']) && (string)$flags['discharge'] === '1';
                $cm  = !empty($flags['close_management']) && (string)$flags['close_management'] === '1';
                $pdf = !empty($flags['generate_pdf']) && (string)$flags['generate_pdf'] === '1';

                $up->execute(array(
                    ':uid' => (int)$uid,
                    ':v'   => $v ? 1 : 0,
                    ':c'   => $c ? 1 : 0,
                    ':e'   => $e ? 1 : 0,
                    ':d'   => $d ? 1 : 0,
                    ':dis' => $dis ? 1 : 0,
                    ':cm'  => $cm ? 1 : 0,
                    ':pdf' => $pdf ? 1 : 0,
                ));
            }
            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /** true/false para uma ação (create|edit|delete) */
    public function userCan(int $userId, string $action): bool
    {
        $a = strtolower($action);
        if ($a === 'create') {
            $col = self::COL_CREATE;
        } elseif ($a === 'view' || $a === 'visualizar') {
            $col = self::COL_VIEW;
        } elseif ($a === 'edit') {
            $col = self::COL_EDIT;
        } elseif ($a === 'delete') {
            $col = self::COL_DELETE;
        } elseif (in_array($a, ['discharge', 'dar_alta', 'alta'], true)) {
            $col = self::COL_DISCHARGE;
        } elseif (in_array($a, ['close_management', 'fechar_gestao', 'close_gestao'], true)) {
            $col = self::COL_CLOSE_MANAGEMENT;
        } elseif (in_array($a, ['generate_pdf', 'gerar_pdf', 'pdf'], true)) {
            $col = self::COL_GENERATE_PDF;
        } else {
            return false;
        }

        $sql = "SELECT {$col} FROM " . self::T_PERMS . " WHERE " . self::COL_P_UID . " = :uid";
        $st  = $this->conn->prepare($sql);
        $st->execute([':uid' => $userId]);
        return (bool)$st->fetchColumn();
    }

    /* ========== UTILITÁRIOS (opcionais) ========== */

    /** Garante que todo usuário da tb_user tenha linha em tb_user_permission (INSERT IGNORE) */
    public function syncMissing(): int
    {
        $sql = "INSERT IGNORE INTO " . self::T_PERMS . " (" . self::COL_P_UID . ")
                SELECT " . self::COL_UID . " FROM " . self::T_USERS;
        $count = $this->conn->exec($sql);
        return $count ? (int)$count : 0;
    }

    /** Remove permissões “órfãs” (sem usuário correspondente) — útil sem FK */
    public function deleteOrphans(): int
    {
        $sql = "DELETE p
                FROM " . self::T_PERMS . " p
                LEFT JOIN " . self::T_USERS . " u ON u." . self::COL_UID . " = p." . self::COL_P_UID . "
                WHERE u." . self::COL_UID . " IS NULL";
        $st = $this->conn->prepare($sql);
        $st->execute();
        return (int)$st->rowCount();
    }

    /* ========== HELPERS PRIVADOS ========== */

    /** Retorna apenas IDs que existem em tb_user (com placeholders dinâmicos) */
    private function filterValidUserIds(array $ids): array
    {
        if (!$ids) {
            return array();
        }
        $place = implode(',', array_fill(0, count($ids), '?'));
        $sql   = "SELECT " . self::COL_UID . " FROM " . self::T_USERS . " WHERE " . self::COL_UID . " IN ($place)";
        $st    = $this->conn->prepare($sql);
        // bind por posição
        foreach ($ids as $i => $v) {
            $st->bindValue($i + 1, (int)$v, PDO::PARAM_INT);
        }
        $st->execute();
        $cols = $st->fetchAll(PDO::FETCH_COLUMN);
        return $cols ? array_map('intval', $cols) : array();
    }

    private function ensurePermissionColumns(): void
    {
        try {
            $st = $this->conn->query("SHOW COLUMNS FROM " . self::T_PERMS);
            $cols = $st ? $st->fetchAll(PDO::FETCH_COLUMN) : [];
            $has = array_fill_keys(array_map('strtolower', $cols ?: []), true);

            $toAdd = [];
            if (!isset($has[self::COL_VIEW])) {
                $toAdd[] = "ADD COLUMN " . self::COL_VIEW . " TINYINT(1) NOT NULL DEFAULT 1 AFTER " . self::COL_P_UID;
            }
            if (!isset($has[self::COL_DISCHARGE])) {
                $toAdd[] = "ADD COLUMN " . self::COL_DISCHARGE . " TINYINT(1) NOT NULL DEFAULT 0 AFTER " . self::COL_DELETE;
            }
            if (!isset($has[self::COL_CLOSE_MANAGEMENT])) {
                $toAdd[] = "ADD COLUMN " . self::COL_CLOSE_MANAGEMENT . " TINYINT(1) NOT NULL DEFAULT 0 AFTER " . self::COL_DISCHARGE;
            }
            if (!isset($has[self::COL_GENERATE_PDF])) {
                $toAdd[] = "ADD COLUMN " . self::COL_GENERATE_PDF . " TINYINT(1) NOT NULL DEFAULT 0 AFTER " . self::COL_CLOSE_MANAGEMENT;
            }
            if ($toAdd) {
                $this->conn->exec("ALTER TABLE " . self::T_PERMS . " " . implode(", ", $toAdd));
            }
        } catch (Throwable $e) {
            error_log('[PERMISSION_DAO][SCHEMA] ' . $e->getMessage());
        }
    }
}
