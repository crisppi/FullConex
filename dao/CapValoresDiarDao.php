<?php

declare(strict_types=1);

class CapValoresDiarDAO
{
    private PDO $conn;

    /**
     * Alguns projetos gravam as diárias como uma única linha com colunas
     * `ac_quarto_qtd`, `ac_quarto_cobrado`, etc. Outros salvam cada diária
     * como uma linha (desc_item/qtd/valor_*). Este DAO normaliza os dois
     * formatos para que o formulário de edição receba sempre os campos
     * `ac_*`.
     */
    private const DESC_TO_PREFIX = [
        'quartoapto'     => 'ac_quarto',
        'quarto'         => 'ac_quarto',
        'apartamento'    => 'ac_quarto',
        'dayclinic'      => 'ac_dayclinic',
        'uti'            => 'ac_uti',
        'utisem'         => 'ac_utisemi',
        'utisemi'        => 'ac_utisemi',
        'enfermaria'     => 'ac_enfermaria',
        'bercario'       => 'ac_bercario',
        'acompanha'      => 'ac_acompanhante',
        'acompanhante'   => 'ac_acompanhante',
        'isolamento'     => 'ac_isolamento',
    ];

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function findByCapeante(int $fk_capeante): ?array
    {
        $rows = $this->fetchAllByCapeante($fk_capeante);
        if (!$rows) {
            return null;
        }

        // Se o registro já estiver no formato “colunar” (uma linha com ac_*),
        // apenas devolvemos a primeira linha.
        if ($this->rowLooksStructured($rows[0])) {
            return $rows[0];
        }

        return $this->normalizeLegacyRows($rows);
    }

    private function fetchAllByCapeante(int $fk_capeante): array
    {
        $sql = "SELECT * FROM tb_cap_valores_diar WHERE fk_capeante = :fk";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':fk', $fk_capeante, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function rowLooksStructured(array $row): bool
    {
        // Basta detectar uma coluna esperada nesse formato
        foreach ($row as $key => $value) {
            if (strpos($key, 'ac_quarto_') === 0 || strpos($key, 'ac_dayclinic_') === 0) {
                return true;
            }
        }
        return false;
    }

    private function normalizeLegacyRows(array $rows): array
    {
        $normalized = [];
        $fkCapeante = $rows[0]['fk_capeante'] ?? null;
        if ($fkCapeante !== null) {
            $normalized['fk_capeante'] = (int)$fkCapeante;
        }

        // Inicia com valores vazios para garantir que todos os campos existam.
        foreach (self::DESC_TO_PREFIX as $prefix) {
            $normalized["{$prefix}_qtd"]      = '';
            $normalized["{$prefix}_cobrado"]  = '';
            $normalized["{$prefix}_glosado"]  = '';
            $normalized["{$prefix}_liberado"] = '';
            $normalized["{$prefix}_obs"]      = '';
        }

        foreach ($rows as $row) {
            $descKey = $this->normalizeKey($row['desc_item'] ?? '');
            if (!$descKey || !isset(self::DESC_TO_PREFIX[$descKey])) {
                continue;
            }
            $prefix = self::DESC_TO_PREFIX[$descKey];
            $normalized["{$prefix}_qtd"]      = (string)($row['qtd'] ?? '');
            $normalized["{$prefix}_cobrado"]  = (string)($row['valor_cobrado'] ?? '');
            $normalized["{$prefix}_glosado"]  = (string)($row['valor_glosado'] ?? '');
            $normalized["{$prefix}_liberado"] = (string)($row['valor_liberado'] ?? '');
            $normalized["{$prefix}_obs"]      = (string)($row['obs'] ?? '');
        }

        return $normalized;
    }

    private function normalizeKey(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (function_exists('mb_strtolower')) {
            $value = mb_strtolower($value, 'UTF-8');
        } else {
            $value = strtolower($value);
        }
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c'
        ]);
        $value = preg_replace('/[^a-z0-9]/', '', $value) ?? '';
        return $value;
    }
}
