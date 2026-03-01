<?php
declare(strict_types=1);

/**
 * Carga sintética FullCare
 *
 * Objetivos:
 * - >= 30 hospitais
 * - >= 30 "empresas" (estipulantes + seguradoras)
 * - >= 500 internações
 * - manter apenas 50 internados
 * - média de permanência ~4.5 dias para altas novas (4/5 dias)
 * - gerar contas médicas (tb_capeante) com média ~25.000
 * - gerar TUSS, negociações, prorrogações e eventos adversos
 * - criar usuários médicos/enfermeiros e distribuir internações entre eles
 *
 * Uso:
 *   php scripts/populate_fullcare_mass_data.php
 */

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../db.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "Conexão indisponível.\n");
    exit(1);
}

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

final class Seeder
{
    private PDO $db;
    private array $cols = [];
    private string $now;
    private string $today;
    private string $runToken;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->now = date('Y-m-d H:i:s');
        $this->today = date('Y-m-d');
        $this->runToken = date('YmdHis');
    }

    public function run(): void
    {
        $this->db->beginTransaction();
        try {
            $userAdmin = $this->pickAdminUserId();

            $hospitalIds = $this->ensureHospitals(30, $userAdmin);
            [$seguradoraIds, $estipulanteIds] = $this->ensureCompanies(30, $userAdmin);
            [$medicos, $enfermeiros] = $this->ensureClinicalUsers(10, 10, $userAdmin);

            $internacoesExistentes = $this->countRows('tb_internacao');
            $targetInternacoes = max(500, $internacoesExistentes);
            $novasInternacoes = $targetInternacoes - $internacoesExistentes;

            if ($novasInternacoes > 0) {
                $this->createAdmissions(
                    $novasInternacoes,
                    $hospitalIds,
                    $seguradoraIds,
                    $estipulanteIds,
                    $medicos,
                    $enfermeiros,
                    $userAdmin
                );
            }

            $this->enforceOpenAdmissions(50, $userAdmin);
            $this->ensureCapeantes(25000.0, $userAdmin);
            $this->ensureTuss($userAdmin);
            $this->ensureNegociacoes($userAdmin);
            $this->ensureProrrogacoes($userAdmin);
            $this->ensureEventosAdversos($userAdmin);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $this->printSummary();
    }

    private function pickAdminUserId(): int
    {
        $id = (int) $this->fetchOneValue("SELECT id_usuario FROM tb_user ORDER BY id_usuario ASC LIMIT 1");
        if ($id <= 0) {
            throw new RuntimeException("Nenhum usuário encontrado em tb_user.");
        }
        return $id;
    }

    private function countRows(string $table): int
    {
        return (int) $this->fetchOneValue("SELECT COUNT(*) FROM {$table}");
    }

    private function tableHas(string $table, string $column): bool
    {
        if (!isset($this->cols[$table])) {
            $stmt = $this->db->prepare("
                SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
            ");
            $stmt->execute([':t' => $table]);
            $this->cols[$table] = array_fill_keys(array_column($stmt->fetchAll(), 'COLUMN_NAME'), true);
        }
        return isset($this->cols[$table][$column]);
    }

    private function insertDynamic(string $table, array $data): int
    {
        $filtered = [];
        foreach ($data as $col => $val) {
            if ($this->tableHas($table, $col)) {
                $filtered[$col] = $val;
            }
        }
        if (!$filtered) {
            throw new RuntimeException("Nenhuma coluna válida para insert em {$table}.");
        }

        $cols = array_keys($filtered);
        $ph = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")";
        $stmt = $this->db->prepare($sql);
        foreach ($filtered as $col => $val) {
            if ($val === null) {
                $stmt->bindValue(':' . $col, null, PDO::PARAM_NULL);
            } elseif (is_int($val)) {
                $stmt->bindValue(':' . $col, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $col, (string) $val, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    private function updateDynamic(string $table, array $data, string $where, array $whereParams): void
    {
        $filtered = [];
        foreach ($data as $col => $val) {
            if ($this->tableHas($table, $col)) {
                $filtered[$col] = $val;
            }
        }
        if (!$filtered) {
            return;
        }
        $sets = [];
        foreach (array_keys($filtered) as $col) {
            $sets[] = "{$col} = :set_{$col}";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        foreach ($filtered as $col => $val) {
            $k = ':set_' . $col;
            if ($val === null) {
                $stmt->bindValue($k, null, PDO::PARAM_NULL);
            } elseif (is_int($val)) {
                $stmt->bindValue($k, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, (string) $val, PDO::PARAM_STR);
            }
        }
        foreach ($whereParams as $k => $v) {
            if ($v === null) {
                $stmt->bindValue($k, null, PDO::PARAM_NULL);
            } elseif (is_int($v)) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, (string) $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
    }

    private function fetchOneValue(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function ensureHospitals(int $target, int $userId): array
    {
        $current = $this->fetchAll("
            SELECT id_hospital
            FROM tb_hospital
            WHERE COALESCE(deletado_hosp,'n') <> 's'
            ORDER BY id_hospital ASC
        ");
        $ids = array_map(fn($r) => (int)$r['id_hospital'], $current);
        $need = max(0, $target - count($ids));

        $ufs = ['SP','RJ','MG','PR','SC','RS','GO','BA','PE','DF'];
        for ($i = 1; $i <= $need; $i++) {
            $n = count($ids) + 1;
            $uf = $ufs[array_rand($ufs)];
            $nome = "HOSPITAL FC {$this->runToken} {$n}";
            $id = $this->insertDynamic('tb_hospital', [
                'nome_hosp' => $nome,
                'ativo_hosp' => 's',
                'endereco_hosp' => "Av. Saúde {$n}",
                'numero_hosp' => (string) random_int(10, 9999),
                'bairro_hosp' => 'Centro',
                'cidade_hosp' => 'São Paulo',
                'estado_hosp' => $uf,
                'email01_hosp' => "contato{$n}@hospitalfc.com.br",
                'email02_hosp' => "faturamento{$n}@hospitalfc.com.br",
                'cnpj_hosp' => $this->fakeCnpj($n),
                'telefone01_hosp' => $this->fakePhone(),
                'telefone02_hosp' => $this->fakePhone(),
                'fk_usuario_hosp' => $userId,
                'data_create_hosp' => $this->now,
                'usuario_create_hosp' => 'seed_script',
                'coordenador_medico_hosp' => "Dr. Coordenador {$n}",
                'diretor_hosp' => "Diretor {$n}",
                'coordenador_fat_hosp' => "Coord. Fat {$n}",
                'logo_hosp' => '',
                'deletado_hosp' => 'n',
                'cep_hosp' => $this->fakeCep(),
            ]);
            $ids[] = $id;
        }
        return $ids;
    }

    private function ensureCompanies(int $target, int $userId): array
    {
        $seg = $this->fetchAll("
            SELECT id_seguradora
            FROM tb_seguradora
            WHERE COALESCE(deletado_seg,'n') <> 's'
            ORDER BY id_seguradora ASC
        ");
        $segIds = array_map(fn($r) => (int)$r['id_seguradora'], $seg);
        $needSeg = max(0, $target - count($segIds));

        for ($i = 1; $i <= $needSeg; $i++) {
            $n = count($segIds) + 1;
            $id = $this->insertDynamic('tb_seguradora', [
                'seguradora_seg' => "SEGURADORA FC {$this->runToken} {$n}",
                'endereco_seg' => "Rua Seg {$n}",
                'bairro_seg' => 'Centro',
                'email01_seg' => "atendimento{$n}@segfc.com.br",
                'cnpj_seg' => $this->fakeCnpj($n + 3000),
                'email02_seg' => "financeiro{$n}@segfc.com.br",
                'telefone01_seg' => $this->fakePhone(),
                'telefone02_seg' => $this->fakePhone(),
                'numero_seg' => (string) random_int(1, 9999),
                'cidade_seg' => 'São Paulo',
                'estado_seg' => 'SP',
                'data_create_seg' => $this->now,
                'fk_usuario_seg' => $userId,
                'coordenador_seg' => "Coordenador Seg {$n}",
                'contato_seg' => "Contato Seg {$n}",
                'coord_rh_seg' => "RH Seg {$n}",
                'ativo_seg' => 's',
                'logo_seg' => '',
                'deletado_seg' => 'n',
                'usuario_create_seg' => 'seed_script',
                'valor_alto_custo_seg' => (string) random_int(15000, 40000),
                'dias_visita_seg' => (string) random_int(2, 5),
                'dias_visita_uti_seg' => (string) random_int(1, 3),
                'longa_permanencia_seg' => (string) random_int(10, 20),
                'cep_seg' => $this->fakeCep(),
            ]);
            $segIds[] = $id;
        }

        $est = $this->fetchAll("
            SELECT id_estipulante
            FROM tb_estipulante
            WHERE COALESCE(deletado_est,'n') <> 's'
            ORDER BY id_estipulante ASC
        ");
        $estIds = array_map(fn($r) => (int)$r['id_estipulante'], $est);
        $needEst = max(0, $target - count($estIds));

        for ($i = 1; $i <= $needEst; $i++) {
            $n = count($estIds) + 1;
            $id = $this->insertDynamic('tb_estipulante', [
                'nome_est' => "EMPRESA FC {$this->runToken} {$n}",
                'endereco_est' => "Rua Empresa {$n}",
                'bairro_est' => 'Centro',
                'email01_est' => "contato{$n}@empresafc.com.br",
                'cnpj_est' => $this->fakeCnpj($n + 6000),
                'email02_est' => "rh{$n}@empresafc.com.br",
                'telefone01_est' => $this->fakePhone(),
                'telefone02_est' => $this->fakePhone(),
                'numero_est' => (string) random_int(1, 9000),
                'cidade_est' => 'São Paulo',
                'estado_est' => 'SP',
                'data_create_est' => $this->now,
                'fk_usuario_est' => $userId,
                'logo_est' => '',
                'deletado_est' => 'n',
                'usuario_create_est' => 'seed_script',
                'nome_contato_est' => "Contato Empresa {$n}",
                'nome_responsavel_est' => "Responsável Empresa {$n}",
                'email_contato_est' => "contato{$n}@empresafc.com.br",
                'email_responsavel_est' => "responsavel{$n}@empresafc.com.br",
                'telefone_contato_est' => $this->fakePhone(),
                'telefone_responsavel_est' => $this->fakePhone(),
                'cep_est' => $this->fakeCep(),
            ]);
            $estIds[] = $id;
        }

        return [$segIds, $estIds];
    }

    private function ensureClinicalUsers(int $medTarget, int $enfTarget, int $adminId): array
    {
        $med = $this->fetchAll("SELECT id_usuario FROM tb_user WHERE cargo_user LIKE '%Médico%' OR cargo_user LIKE '%Medico%'");
        $enf = $this->fetchAll("SELECT id_usuario FROM tb_user WHERE cargo_user LIKE '%Enfer%'");
        $medIds = array_map(fn($r) => (int)$r['id_usuario'], $med);
        $enfIds = array_map(fn($r) => (int)$r['id_usuario'], $enf);

        while (count($medIds) < $medTarget) {
            $n = count($medIds) + 1;
            $login = "medico_fc_{$this->runToken}_{$n}";
            $id = $this->insertDynamic('tb_user', [
                'usuario_user' => "Médico FC {$n}",
                'login_user' => $login,
                'sexo_user' => (random_int(0, 1) ? 'M' : 'F'),
                'idade_user' => (string) random_int(28, 62),
                'email_user' => "{$login}@fullcare.com.br",
                'email02_user' => '',
                'senha_user' => password_hash('123456', PASSWORD_DEFAULT),
                'senha_default_user' => 's',
                'endereco_user' => 'Rua Saúde',
                'numero_user' => (string) random_int(10, 999),
                'cidade_user' => 'São Paulo',
                'bairro_user' => 'Centro',
                'estado_user' => 'SP',
                'telefone01_user' => $this->fakePhone(),
                'telefone02_user' => '',
                'data_create_user' => $this->now,
                'usuario_create_user' => 'seed_script',
                'fk_usuario_user' => $adminId,
                'ativo_user' => 's',
                'data_admissao_user' => date('Y-m-d', strtotime('-' . random_int(10, 800) . ' days')),
                'vinculo_user' => 'CLT',
                'nivel_user' => '3',
                'cargo_user' => 'Médico',
                'depto_user' => 'Assistencial',
                'cpf_user' => $this->fakeCpf(),
                'obs_user' => 'Usuário criado para distribuição de internações',
                'tipo_reg_user' => 'CRM',
                'reg_profissional_user' => (string) random_int(10000, 99999),
                'foto_usuario' => '',
            ]);
            $medIds[] = $id;
        }

        while (count($enfIds) < $enfTarget) {
            $n = count($enfIds) + 1;
            $login = "enfer_fc_{$this->runToken}_{$n}";
            $id = $this->insertDynamic('tb_user', [
                'usuario_user' => "Enfermeiro FC {$n}",
                'login_user' => $login,
                'sexo_user' => (random_int(0, 1) ? 'M' : 'F'),
                'idade_user' => (string) random_int(24, 58),
                'email_user' => "{$login}@fullcare.com.br",
                'email02_user' => '',
                'senha_user' => password_hash('123456', PASSWORD_DEFAULT),
                'senha_default_user' => 's',
                'endereco_user' => 'Rua Saúde',
                'numero_user' => (string) random_int(10, 999),
                'cidade_user' => 'São Paulo',
                'bairro_user' => 'Centro',
                'estado_user' => 'SP',
                'telefone01_user' => $this->fakePhone(),
                'telefone02_user' => '',
                'data_create_user' => $this->now,
                'usuario_create_user' => 'seed_script',
                'fk_usuario_user' => $adminId,
                'ativo_user' => 's',
                'data_admissao_user' => date('Y-m-d', strtotime('-' . random_int(10, 800) . ' days')),
                'vinculo_user' => 'CLT',
                'nivel_user' => '3',
                'cargo_user' => 'Enfermeiro',
                'depto_user' => 'Assistencial',
                'cpf_user' => $this->fakeCpf(),
                'obs_user' => 'Usuário criado para distribuição de internações',
                'tipo_reg_user' => 'COREN',
                'reg_profissional_user' => (string) random_int(10000, 99999),
                'foto_usuario' => '',
            ]);
            $enfIds[] = $id;
        }

        return [$medIds, $enfIds];
    }

    private function createAdmissions(
        int $qtd,
        array $hospitalIds,
        array $seguradoraIds,
        array $estipulanteIds,
        array $medicos,
        array $enfermeiros,
        int $adminId
    ): void {
        $acomodacoes = ['APTO', 'ENFERMARIA', 'UTI', 'SEMI-INTENSIVA'];
        $tipos = ['CLÍNICA MÉDICA', 'CIRÚRGICA', 'UTI', 'PEDIÁTRICA'];
        $modos = ['ELETIVA', 'URGÊNCIA', 'EMERGÊNCIA'];
        $origens = ['PRONTO SOCORRO', 'AMBULATÓRIO', 'TRANSFERÊNCIA'];

        for ($i = 1; $i <= $qtd; $i++) {
            $pacId = $this->insertDynamic('tb_paciente', [
                'nome_pac' => "PACIENTE FC {$this->runToken} {$i}",
                'nome_social_pac' => '',
                'cpf_pac' => $this->fakeCpf(),
                'data_nasc_pac' => date('Y-m-d', strtotime('-' . random_int(18 * 365, 90 * 365) . ' days')),
                'sexo_pac' => random_int(0, 1) ? 'M' : 'F',
                'mae_pac' => "Mãe Paciente {$i}",
                'endereco_pac' => "Rua Paciente {$i}",
                'numero_pac' => (string) random_int(1, 9999),
                'bairro_pac' => 'Centro',
                'cidade_pac' => 'São Paulo',
                'estado_pac' => 'SP',
                'complemento_pac' => '',
                'email01_pac' => "paciente{$this->runToken}{$i}@mail.com",
                'email02_pac' => '',
                'telefone01_pac' => $this->fakePhone(),
                'telefone02_pac' => '',
                'ativo_pac' => 's',
                'data_create_pac' => $this->now,
                'fk_usuario_pac' => $adminId,
                'fk_estipulante_pac' => $estipulanteIds[array_rand($estipulanteIds)],
                'fk_seguradora_pac' => $seguradoraIds[array_rand($seguradoraIds)],
                'obs_pac' => 'Paciente gerado via script de carga',
                'matricula_pac' => 'MAT' . $this->runToken . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'usuario_create_pac' => 'seed_script',
                'deletado_pac' => 'n',
                'cep_pac' => $this->fakeCep(),
                'num_atendimento_pac' => (string) random_int(100000, 999999),
                'recem_nascido_pac' => 'n',
                'mae_titular_pac' => '',
                'matricula_titular_pac' => '',
                'numero_rn_pac' => null,
            ]);

            $internDate = date('Y-m-d H:i:s', strtotime('-' . random_int(10, 180) . ' days +' . random_int(0, 20) . ' hours'));
            $medicoId = $medicos[array_rand($medicos)];
            $enfId = $enfermeiros[array_rand($enfermeiros)];

            $this->insertDynamic('tb_internacao', [
                'fk_hospital_int' => $hospitalIds[array_rand($hospitalIds)],
                'fk_paciente_int' => $pacId,
                'rel_int' => 'Internação criada por carga automática',
                'fk_patologia_int' => 1,
                'fk_cid_int' => 1,
                'fk_patologia2' => 1,
                'data_intern_int' => $internDate,
                'data_lancamento_int' => $internDate,
                'acoes_int' => '',
                'internado_int' => 's',
                'modo_internacao_int' => $modos[array_rand($modos)],
                'tipo_admissao_int' => $tipos[array_rand($tipos)],
                'titular_int' => 'Dr. Titular',
                'crm_int' => (string) random_int(10000, 99999),
                'data_visita_int' => date('Y-m-d', strtotime($internDate . ' +1 day')),
                'grupo_patologia_int' => 'CLÍNICO',
                'data_create_int' => $this->now,
                'usuario_create_int' => 'seed_script',
                'primeira_vis_int' => 's',
                'visita_no_int' => 1,
                'visita_enf_int' => 's',
                'visita_med_int' => 's',
                'senha_int' => 'SNH' . random_int(100000, 999999),
                'acomodacao_int' => $acomodacoes[array_rand($acomodacoes)],
                'visita_auditor_prof_med' => "medico:{$medicoId}",
                'visita_auditor_prof_enf' => "enf:{$enfId}",
                'fk_usuario_int' => $medicoId,
                'censo_int' => 'n',
                'especialidade_int' => 'CLÍNICA MÉDICA',
                'programacao_int' => '',
                'origem_int' => $origens[array_rand($origens)],
                'int_pertinente_int' => 's',
                'rel_pertinente_int' => '',
                'hora_intern_int' => date('H:i:s', strtotime($internDate)),
                'num_atendimento_int' => (string) random_int(100000, 999999),
                'timer_int' => random_int(5, 120),
            ]);
        }
    }

    private function enforceOpenAdmissions(int $openTarget, int $userId): void
    {
        $all = $this->fetchAll("
            SELECT id_internacao, data_intern_int
            FROM tb_internacao
            ORDER BY data_intern_int DESC, id_internacao DESC
        ");

        if (!$all) {
            return;
        }

        $openIds = array_slice(array_column($all, 'id_internacao'), 0, $openTarget);
        $openMap = array_fill_keys(array_map('intval', $openIds), true);

        foreach ($all as $idx => $row) {
            $idInt = (int) $row['id_internacao'];
            $internDate = (string) $row['data_intern_int'];
            $keepOpen = isset($openMap[$idInt]);

            $this->updateDynamic('tb_internacao', [
                'internado_int' => $keepOpen ? 's' : 'n'
            ], 'id_internacao = :id', [':id' => $idInt]);

            if ($keepOpen) {
                continue;
            }

            $alreadyAlta = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_alta WHERE fk_id_int_alt = :id",
                [':id' => $idInt]
            ) > 0;
            if ($alreadyAlta) {
                continue;
            }

            $dur = ($idx % 2 === 0) ? 4 : 5;
            $altaDate = date('Y-m-d H:i:s', strtotime($internDate . " +{$dur} days"));
            if ($altaDate > $this->now) {
                $altaDate = date('Y-m-d H:i:s', strtotime('-1 day'));
            }

            $this->insertDynamic('tb_alta', [
                'fk_id_int_alt' => $idInt,
                'tipo_alta_alt' => 'ALTA MÉDICA',
                'internado_alt' => 'n',
                'usuario_alt' => 'seed_script',
                'data_create_alt' => $this->now,
                'data_alta_alt' => substr($altaDate, 0, 10),
                'hora_alta_alt' => substr($altaDate, 11, 8),
                'fk_usuario_alt' => $userId,
            ]);
        }
    }

    private function ensureCapeantes(float $mediaAlvo, int $userId): void
    {
        $ids = $this->fetchAll("
            SELECT i.id_internacao, i.data_intern_int, a.data_alta_alt
            FROM tb_internacao i
            INNER JOIN tb_alta a ON a.fk_id_int_alt = i.id_internacao
        ");

        foreach ($ids as $row) {
            $idInt = (int) $row['id_internacao'];
            $exists = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_capeante WHERE fk_int_capeante = :id",
                [':id' => $idInt]
            ) > 0;
            if ($exists) {
                continue;
            }

            $base = $mediaAlvo + random_int(-5000, 5000);
            $apresentado = max(15000, $base + random_int(-1500, 1500));
            $glosa = max(0, (int) round($apresentado * (random_int(2, 9) / 100)));
            $final = $apresentado - $glosa;

            $dataIni = substr((string)$row['data_intern_int'], 0, 10);
            $dataFim = substr((string)$row['data_alta_alt'], 0, 10);
            $diarias = max(1, (int) $this->fetchOneValue(
                "SELECT GREATEST(1, DATEDIFF(:fim, :ini))",
                [':ini' => $dataIni, ':fim' => $dataFim]
            ));

            $this->insertDynamic('tb_capeante', [
                'aud_adm_capeante' => 's',
                'aud_enf_capeante' => 's',
                'aud_med_capeante' => 's',
                'data_fech_capeante' => $dataFim,
                'data_digit_capeante' => $this->today,
                'data_final_capeante' => $dataFim,
                'data_inicial_capeante' => $dataIni,
                'diarias_capeante' => $diarias,
                'lote_cap' => 'L' . $this->runToken,
                'glosa_diaria' => random_int(0, 500),
                'glosa_honorarios' => random_int(0, 1000),
                'glosa_matmed' => random_int(0, 700),
                'glosa_oxig' => random_int(0, 300),
                'glosa_sadt' => random_int(0, 900),
                'glosa_taxas' => random_int(0, 300),
                'glosa_opme' => random_int(0, 700),
                'med_check' => 's',
                'enfer_check' => 's',
                'adm_check' => 's',
                'pacote' => 'n',
                'parcial_capeante' => 'n',
                'parcial_num' => 0,
                'acomodacao_cap' => 'ENFERMARIA',
                'fk_int_capeante' => $idInt,
                'fk_user_cap' => $userId,
                'valor_apresentado_capeante' => number_format($apresentado, 2, '.', ''),
                'valor_diarias' => number_format($apresentado * 0.45, 2, '.', ''),
                'valor_final_capeante' => number_format($final, 2, '.', ''),
                'valor_glosa_enf' => number_format($glosa * 0.30, 2, '.', ''),
                'valor_glosa_med' => number_format($glosa * 0.35, 2, '.', ''),
                'valor_glosa_total' => number_format($glosa, 2, '.', ''),
                'valor_honorarios' => number_format($apresentado * 0.20, 2, '.', ''),
                'valor_matmed' => number_format($apresentado * 0.18, 2, '.', ''),
                'valor_oxig' => number_format($apresentado * 0.03, 2, '.', ''),
                'valor_sadt' => number_format($apresentado * 0.12, 2, '.', ''),
                'valor_opme' => number_format($apresentado * 0.02, 2, '.', ''),
                'senha_finalizada' => 's',
                'desconto_valor_cap' => 0,
                'negociado_desconto_cap' => 'n',
                'em_auditoria_cap' => 'n',
                'aberto_cap' => 'n',
                'encerrado_cap' => 's',
                'valor_taxa' => number_format($apresentado * 0.02, 2, '.', ''),
                'usuario_create_cap' => 'seed_script',
                'data_create_cap' => $this->now,
                'conta_parada_cap' => 'n',
                'parada_motivo_cap' => '',
                'timer_start_cap' => $this->now,
                'timer_end_cap' => $this->now,
                'timer_cap' => random_int(10, 250),
                'fk_id_aud_enf' => $userId,
                'fk_id_aud_med' => $userId,
                'fk_id_aud_adm' => $userId,
                'fk_id_aud_hosp' => $userId,
                'valor_medicamentos' => number_format($apresentado * 0.15, 2, '.', ''),
                'valor_materiais' => number_format($apresentado * 0.10, 2, '.', ''),
                'glosa_medicamentos' => number_format($glosa * 0.15, 2, '.', ''),
                'glosa_materiais' => number_format($glosa * 0.10, 2, '.', ''),
            ]);
        }
    }

    private function ensureTuss(int $userId): void
    {
        $closed = $this->fetchAll("
            SELECT i.id_internacao
            FROM tb_internacao i
            WHERE i.internado_int = 'n'
            ORDER BY i.id_internacao DESC
        ");

        $codes = ['10101012', '20102035', '40304011', '40808170', '41001013'];
        foreach ($closed as $idx => $r) {
            if ($idx % 3 === 0) {
                continue;
            }
            $idInt = (int) $r['id_internacao'];
            $exists = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_tuss WHERE fk_int_tuss = :id",
                [':id' => $idInt]
            );
            if ($exists > 0) {
                continue;
            }
            $qtd = random_int(1, 3);
            $lib = max(0, $qtd - random_int(0, 1));
            $this->insertDynamic('tb_tuss', [
                'fk_usuario_tuss' => $userId,
                'fk_int_tuss' => $idInt,
                'fk_vis_tuss' => null,
                'data_create_tuss' => $this->now,
                'tuss_solicitado' => $codes[array_rand($codes)],
                'tuss_liberado_sn' => ($lib === $qtd ? 's' : 'n'),
                'qtd_tuss_solicitado' => $qtd,
                'qtd_tuss_liberado' => $lib,
                'data_realizacao_tuss' => $this->today,
                'glosa_tuss' => ($lib < $qtd ? random_int(50, 600) : 0),
            ]);
        }
    }

    private function ensureNegociacoes(int $userId): void
    {
        $closed = $this->fetchAll("
            SELECT id_internacao
            FROM tb_internacao
            WHERE internado_int = 'n'
            ORDER BY id_internacao DESC
            LIMIT 300
        ");
        $tipos = ['TROCA UTI/SEMI', 'TROCA APTO/ENFERMARIA', 'PACOTE DIÁRIA'];
        $trocas = [
            ['UTI', 'SEMI'],
            ['APTO', 'ENFERMARIA'],
            ['SEMI', 'ENFERMARIA']
        ];
        foreach ($closed as $idx => $r) {
            if ($idx % 2 !== 0) {
                continue;
            }
            $idInt = (int) $r['id_internacao'];
            $exists = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_negociacao WHERE fk_id_int = :id",
                [':id' => $idInt]
            );
            if ($exists > 0) {
                continue;
            }
            $swap = $trocas[array_rand($trocas)];
            $qtd = random_int(1, 5);
            $saving = $qtd * random_int(300, 1500);
            $ini = date('Y-m-d', strtotime('-' . random_int(20, 120) . ' days'));
            $fim = date('Y-m-d', strtotime($ini . ' +' . random_int(1, 6) . ' days'));
            $this->insertDynamic('tb_negociacao', [
                'fk_id_int' => $idInt,
                'troca_de' => $swap[0],
                'troca_para' => $swap[1],
                'qtd' => $qtd,
                'saving' => number_format((float)$saving, 2, '.', ''),
                'fk_usuario_neg' => $userId,
                'data_inicio_neg' => $ini,
                'data_fim_neg' => $fim,
                'tipo_negociacao' => $tipos[array_rand($tipos)],
            ]);
        }
    }

    private function ensureProrrogacoes(int $userId): void
    {
        $open = $this->fetchAll("
            SELECT id_internacao, data_intern_int
            FROM tb_internacao
            WHERE internado_int = 's'
            ORDER BY data_intern_int ASC
        ");
        foreach ($open as $idx => $r) {
            if ($idx % 3 !== 0) {
                continue;
            }
            $idInt = (int)$r['id_internacao'];
            $exists = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_prorrogacao WHERE fk_internacao_pror = :id",
                [':id' => $idInt]
            );
            if ($exists > 0) {
                continue;
            }
            $ini = date('Y-m-d', strtotime((string)$r['data_intern_int'] . ' +2 days'));
            $fim = date('Y-m-d', strtotime($ini . ' +' . random_int(2, 5) . ' days'));
            $diarias = max(1, (int)$this->fetchOneValue(
                "SELECT GREATEST(1, DATEDIFF(:fim,:ini))",
                [':ini' => $ini, ':fim' => $fim]
            ));
            $this->insertDynamic('tb_prorrogacao', [
                'fk_internacao_pror' => $idInt,
                'fk_visita_pror' => null,
                'acomod1_pror' => 'ENFERMARIA',
                'isol_1_pror' => 'n',
                'prorrog1_fim_pror' => $fim,
                'prorrog1_ini_pror' => $ini,
                'fk_usuario_pror' => $userId,
                'diarias_1' => $diarias,
            ]);
        }
    }

    private function ensureEventosAdversos(int $userId): void
    {
        $ints = $this->fetchAll("
            SELECT id_internacao
            FROM tb_internacao
            ORDER BY id_internacao DESC
            LIMIT 250
        ");
        $tipos = ['QUEDA', 'ERRO MEDICAÇÃO', 'INFECÇÃO', 'LESÃO PELE', 'EVENTO SENTINELA'];
        foreach ($ints as $idx => $r) {
            if ($idx % 6 !== 0) {
                continue;
            }
            $idInt = (int)$r['id_internacao'];
            $exists = (int) $this->fetchOneValue(
                "SELECT COUNT(*) FROM tb_gestao WHERE fk_internacao_ges = :id",
                [':id' => $idInt]
            );
            if ($exists > 0) {
                continue;
            }
            $this->insertDynamic('tb_gestao', [
                'fk_internacao_ges' => $idInt,
                'fk_visita_ges' => null,
                'evento_adverso_ges' => 's',
                'rel_evento_adverso_ges' => 'Evento adverso gerado em carga de homologação',
                'tipo_evento_adverso_gest' => $tipos[array_rand($tipos)],
                'evento_sinalizado_ges' => 's',
                'evento_discutido_ges' => 's',
                'evento_negociado_ges' => (random_int(0, 1) ? 's' : 'n'),
                'evento_valor_negoc_ges' => number_format((float) random_int(0, 5000), 2, '.', ''),
                'evento_prorrogar_ges' => (random_int(0, 1) ? 's' : 'n'),
                'evento_fech_ges' => 's',
                'evento_retorno_qual_hosp_ges' => '',
                'evento_classificado_hospital_ges' => 's',
                'evento_data_ges' => $this->today,
                'evento_encerrar_ges' => 's',
                'evento_impacto_financ_ges' => number_format((float) random_int(500, 8000), 2, '.', ''),
                'evento_prolongou_internacao_ges' => (random_int(0, 1) ? 's' : 'n'),
                'evento_concluido_ges' => 's',
                'evento_classificacao_ges' => 'MODERADO',
                'fk_user_ges' => $userId,
            ]);
        }
    }

    private function printSummary(): void
    {
        $hosp = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_hospital WHERE COALESCE(deletado_hosp,'n') <> 's'");
        $seg = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_seguradora WHERE COALESCE(deletado_seg,'n') <> 's'");
        $est = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_estipulante WHERE COALESCE(deletado_est,'n') <> 's'");
        $ints = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_internacao");
        $open = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_internacao WHERE internado_int = 's'");
        $caps = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_capeante");
        $tuss = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_tuss");
        $nego = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_negociacao");
        $pror = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_prorrogacao");
        $ea = (int) $this->fetchOneValue("SELECT COUNT(*) FROM tb_gestao WHERE COALESCE(evento_adverso_ges,'n') = 's'");

        $avgStay = $this->fetchOneValue("
            SELECT ROUND(AVG(GREATEST(1, DATEDIFF(a.data_alta_alt, DATE(i.data_intern_int)))), 2)
            FROM tb_internacao i
            INNER JOIN tb_alta a ON a.fk_id_int_alt = i.id_internacao
            WHERE a.data_alta_alt IS NOT NULL
        ");
        $avgCost = $this->fetchOneValue("
            SELECT ROUND(AVG(COALESCE(valor_final_capeante,0)), 2)
            FROM tb_capeante
            WHERE valor_final_capeante IS NOT NULL
        ");

        echo "=== Carga concluída ===\n";
        echo "Hospitais: {$hosp}\n";
        echo "Seguradoras: {$seg}\n";
        echo "Estipulantes (empresas): {$est}\n";
        echo "Internações total: {$ints}\n";
        echo "Internados (abertos): {$open}\n";
        echo "Capeantes: {$caps}\n";
        echo "TUSS: {$tuss}\n";
        echo "Negociações: {$nego}\n";
        echo "Prorrogações: {$pror}\n";
        echo "Eventos adversos: {$ea}\n";
        echo "Média permanência (dias): {$avgStay}\n";
        echo "Média valor conta: {$avgCost}\n";
    }

    private function fakeCpf(): string
    {
        $n = str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $n);
    }

    private function fakeCnpj(int $seed): string
    {
        $base = str_pad((string)(10000000000000 + $seed), 14, '0', STR_PAD_LEFT);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $base);
    }

    private function fakePhone(): string
    {
        return sprintf("(%02d) 9%04-%04d", random_int(11, 99), random_int(1000, 9999), random_int(1000, 9999));
    }

    private function fakeCep(): string
    {
        return sprintf("%05d-%03d", random_int(10000, 99999), random_int(100, 999));
    }
}

try {
    $seeder = new Seeder($conn);
    $seeder->run();
} catch (Throwable $e) {
    fwrite(STDERR, "Erro na carga: " . $e->getMessage() . "\n");
    exit(1);
}

