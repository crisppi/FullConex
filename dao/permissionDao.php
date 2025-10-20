<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/permission.php';

final class PermissionDAO
{
    private PDO $conn;
    private string $baseUrl;

    // nomes de tabela/colunas centralizados
    private const T_USERS      = 'tb_user';
    private const COL_UID      = 'id_usuario';
    private const COL_NAME     = 'usuario_user';
    private const COL_EMAIL    = 'email_user';

    private const T_PERMS      = 'tb_user_permission';
    private const COL_P_UID    = 'user_id';
    private const COL_CREATE   = 'can_create';
    private const COL_EDIT     = 'can_edit';
    private const COL_DELETE   = 'can_delete';
    private const COL_UPDATED  = 'updated_at';

    public function __construct(PDO $conn, string $baseUrl)
    {
        $this->conn    = $conn;
        $this->baseUrl = $baseUrl;

        // PDO em modo seguro (caso não esteja no bootstrap)
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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
                IFNULL(p." . self::COL_CREATE . ", 0) AS " . self::COL_CREATE . ",
                IFNULL(p." . self::COL_EDIT . ",   0) AS " . self::COL_EDIT . ",
                IFNULL(p." . self::COL_DELETE . ", 0) AS " . self::COL_DELETE . ",
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
        $sql = "SELECT " . self::COL_CREATE . ", " . self::COL_EDIT . ", " . self::COL_DELETE . ", " . self::COL_UPDATED . "
                FROM " . self::T_PERMS . "
                WHERE " . self::COL_P_UID . " = :uid";
        $st  = $this->conn->prepare($sql);
        $st->execute([':uid' => $userId]);
        $row = $st->fetch();

        $p = new Permission();
        $p->user_id    = $userId;
        $p->can_create = isset($row[self::COL_CREATE]) ? (int)$row[self::COL_CREATE] : 0;
        $p->can_edit   = isset($row[self::COL_EDIT])   ? (int)$row[self::COL_EDIT]   : 0;
        $p->can_delete = isset($row[self::COL_DELETE]) ? (int)$row[self::COL_DELETE] : 0;
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
     * Atualiza em lote a matriz de permissões:
     *   $permMatrix = [ userId => ['create'=>'1|0','edit'=>'1|0','delete'=>'1|0'], ... ]
     * Filtra IDs válidos (existentes em tb_user) e faz tudo em transação.
     */
    public function bulkUpdate(array $permMatrix): void
    {
        if (empty($permMatrix)) return;

        // 1) Seleciona IDs válidos na tb_user
        $ids = array_values(array_map('intval', array_keys($permMatrix)));
        $valid = $this->filterValidUserIds($ids);
        if (!$valid) return;

        // 2) Upsert em transação
        $this->conn->beginTransaction();
        try {
            $sql = "INSERT INTO " . self::T_PERMS . "
                        (" . self::COL_P_UID . ", " . self::COL_CREATE . ", " . self::COL_EDIT . ", " . self::COL_DELETE . ")
                    VALUES (:uid,:c,:e,:d)
                    ON DUPLICATE KEY UPDATE
                        " . self::COL_CREATE . " = VALUES(" . self::COL_CREATE . "),
                        " . self::COL_EDIT . "   = VALUES(" . self::COL_EDIT . "),
                        " . self::COL_DELETE . " = VALUES(" . self::COL_DELETE . ")";
            $up = $this->conn->prepare($sql);

            foreach ($valid as $uid) {
                $flags = $permMatrix[$uid] ?? [];
                $c = !empty($flags['create']) && $flags['create'] == '1';
                $e = !empty($flags['edit'])   && $flags['edit']   == '1';
                $d = !empty($flags['delete']) && $flags['delete'] == '1';
                $up->execute([
                    ':uid' => $uid,
                    ':c'   => $c ? 1 : 0,
                    ':e'   => $e ? 1 : 0,
                    ':d'   => $d ? 1 : 0,
                ]);
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
        $col = match (strtolower($action)) {
            'create' => self::COL_CREATE,
            'edit'   => self::COL_EDIT,
            'delete' => self::COL_DELETE,
            default  => null
        };
        if (!$col) return false;

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
        return $this->conn->exec($sql) ?: 0;
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
        return $st->rowCount();
    }

    /* ========== HELPERS PRIVADOS ========== */

    /** Retorna apenas IDs que existem em tb_user (com placeholders dinâmicos) */
    private function filterValidUserIds(array $ids): array
    {
        if (!$ids) return [];
        $place = implode(',', array_fill(0, count($ids), '?'));
        $sql   = "SELECT " . self::COL_UID . " FROM " . self::T_USERS . " WHERE " . self::COL_UID . " IN ($place)";
        $st    = $this->conn->prepare($sql);
        // bind por posição
        foreach ($ids as $i => $v) $st->bindValue($i + 1, $v, PDO::PARAM_INT);
        $st->execute();
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }
}