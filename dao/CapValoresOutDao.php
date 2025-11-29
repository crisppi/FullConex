<?php
// dao/CapValoresOutDAO.php
declare(strict_types=1);

class CapValoresOutDAO
{
    private PDO $conn;
    private string $url;

    public function __construct(PDO $conn, string $url = '')
    {
        $this->conn = $conn;
        $this->url  = $url;
    }

    /** Retorna array (ou null) com os campos do OUTROS para um capeante */
    public function findByCapeante(int $fk_capeante): ?array
    {
        $sql = "SELECT * FROM tb_cap_valores_out WHERE fk_capeante = :fk LIMIT 1";
        $st  = $this->conn->prepare($sql);
        $st->bindValue(':fk', $fk_capeante, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * UPSERT: 1 linha por capeante.
     * Se não existir, cria; se existir, atualiza.
     */
    public function upsert(array $d): bool
    {
        $sql = "INSERT INTO tb_cap_valores_out (
                    fk_capeante, fk_int_capeante,

                    outros_pacote_qtd, outros_pacote_cobrado, outros_pacote_glosado, outros_pacote_liberado, outros_pacote_obs,
                    outros_remocao_qtd, outros_remocao_cobrado, outros_remocao_glosado, outros_remocao_liberado, outros_remocao_obs,
                    outros_desconto_out, comentarios_obs
                ) VALUES (
                    :fk_capeante, :fk_int_capeante,

                    :outros_pacote_qtd, :outros_pacote_cobrado, :outros_pacote_glosado, :outros_pacote_liberado, :outros_pacote_obs,
                    :outros_remocao_qtd, :outros_remocao_cobrado, :outros_remocao_glosado, :outros_remocao_liberado, :outros_remocao_obs,
                    :outros_desconto_out, :comentarios_obs
                )
                ON DUPLICATE KEY UPDATE
                    fk_int_capeante         = VALUES(fk_int_capeante),

                    outros_pacote_qtd       = VALUES(outros_pacote_qtd),
                    outros_pacote_cobrado   = VALUES(outros_pacote_cobrado),
                    outros_pacote_glosado   = VALUES(outros_pacote_glosado),
                    outros_pacote_liberado  = VALUES(outros_pacote_liberado),
                    outros_pacote_obs       = VALUES(outros_pacote_obs),

                    outros_remocao_qtd      = VALUES(outros_remocao_qtd),
                    outros_remocao_cobrado  = VALUES(outros_remocao_cobrado),
                    outros_remocao_glosado  = VALUES(outros_remocao_glosado),
                    outros_remocao_liberado = VALUES(outros_remocao_liberado),
                    outros_remocao_obs      = VALUES(outros_remocao_obs),
                    outros_desconto_out     = VALUES(outros_desconto_out),
                    comentarios_obs         = VALUES(comentarios_obs)
        ";

        $st = $this->conn->prepare($sql);

        // binds obrigatórios (capeante / internacao)
        $st->bindValue(':fk_capeante',     (int)$d['fk_capeante'],     PDO::PARAM_INT);
        $st->bindValue(':fk_int_capeante', (int)$d['fk_int_capeante'], PDO::PARAM_INT);

        // Pacote
        $st->bindValue(':outros_pacote_qtd',       self::toInt($d['outros_pacote_qtd'] ?? null), PDO::PARAM_INT);
        $st->bindValue(':outros_pacote_cobrado',   self::toDec($d['outros_pacote_cobrado'] ?? null));
        $st->bindValue(':outros_pacote_glosado',   self::toDec($d['outros_pacote_glosado'] ?? null));
        $st->bindValue(':outros_pacote_liberado',  self::toDec($d['outros_pacote_liberado'] ?? null));
        $st->bindValue(':outros_pacote_obs',       (string)($d['outros_pacote_obs'] ?? ''));

        // Remoção
        $st->bindValue(':outros_remocao_qtd',      self::toInt($d['outros_remocao_qtd'] ?? null), PDO::PARAM_INT);
        $st->bindValue(':outros_remocao_cobrado',  self::toDec($d['outros_remocao_cobrado'] ?? null));
        $st->bindValue(':outros_remocao_glosado',  self::toDec($d['outros_remocao_glosado'] ?? null));
        $st->bindValue(':outros_remocao_liberado', self::toDec($d['outros_remocao_liberado'] ?? null));
        $st->bindValue(':outros_remocao_obs',      (string)($d['outros_remocao_obs'] ?? ''));
        $st->bindValue(':outros_desconto_out',     self::toDec($d['outros_desconto_out'] ?? null));
        $st->bindValue(':comentarios_obs',         (string)($d['comentarios_obs'] ?? ''));

        return $st->execute();
    }

    /* ===== Helpers numéricos (mesmo critério do restante do projeto) ===== */
    private static function toInt($v): ?int
    {
        if ($v === '' || $v === null) return null;
        return (int)$v;
    }

    private static function toDec($v): ?float
    {
        if ($v === '' || $v === null) return null;
        $s = (string)$v;
        $s = str_replace(['R$', ' ', '.'], '', $s);
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float)$s : null;
    }
}
