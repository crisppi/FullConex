<?php
require_once("templates/header.php");
require_once("models/message.php");

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fmt_date_br($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '-';
    }
    $dateOnly = substr($raw, 0, 10);
    if ($dt = DateTime::createFromFormat('Y-m-d', $dateOnly)) {
        return $dt->format('d/m/Y');
    }
    $ts = strtotime($raw);
    return $ts ? date('d/m/Y', $ts) : $raw;
}

$dt_ini = filter_input(INPUT_GET, 'dt_ini', FILTER_SANITIZE_SPECIAL_CHARS);
$dt_fim = filter_input(INPUT_GET, 'dt_fim', FILTER_SANITIZE_SPECIAL_CHARS);
$seguradora_id = filter_input(INPUT_GET, 'seguradora_id', FILTER_VALIDATE_INT);
$responsavel = trim((string) filter_input(INPUT_GET, 'responsavel', FILTER_SANITIZE_SPECIAL_CHARS));

$seguradoras = [];
try {
    $seguradoras = $conn->query("SELECT id_seguradora, seguradora_seg FROM tb_seguradora WHERE deletado_seg <> 's' OR deletado_seg IS NULL ORDER BY seguradora_seg")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $th) {
    $seguradoras = [];
}

$visitasPendentes = [];
$contasPendentes = [];
$visitasErro = null;
$contasErro = null;

try {
    $visitaWhere = ["vi.id_visita IS NULL"];
    $visitaParams = [];

    if ($dt_ini) {
        $visitaWhere[] = "DATE(ac.data_intern_int) >= :v_dt_ini";
        $visitaParams[':v_dt_ini'] = $dt_ini;
    }
    if ($dt_fim) {
        $visitaWhere[] = "DATE(ac.data_intern_int) <= :v_dt_fim";
        $visitaParams[':v_dt_fim'] = $dt_fim;
    }
    if ($seguradora_id) {
        $visitaWhere[] = "pa.fk_seguradora_pac = :v_seguradora_id";
        $visitaParams[':v_seguradora_id'] = $seguradora_id;
    }
    if ($responsavel !== '') {
        $visitaWhere[] = "ac.usuario_create_int LIKE :v_resp";
        $visitaParams[':v_resp'] = "%" . $responsavel . "%";
    }

    $visitaSql = "
        SELECT
            ac.id_internacao,
            ac.data_intern_int,
            ac.usuario_create_int,
            pa.nome_pac,
            ho.nome_hosp,
            se.seguradora_seg
        FROM tb_internacao ac
        LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
        LEFT JOIN tb_hospital ho ON ho.id_hospital = ac.fk_hospital_int
        LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
        LEFT JOIN tb_visita vi
            ON vi.fk_internacao_vis = ac.id_internacao
            AND (vi.retificado IS NULL OR vi.retificado IN (0, '0', '', 'n', 'N'))
        WHERE " . implode(" AND ", $visitaWhere) . "
        ORDER BY ac.data_intern_int DESC
        LIMIT 200
    ";

    $stmt = $conn->prepare($visitaSql);
    foreach ($visitaParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $visitasPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $th) {
    $visitasErro = $th->getMessage();
}

try {
    $contaWhere = [
        "(ca.conta_faturada_cap IS NULL OR ca.conta_faturada_cap IN ('', 'n', 'N', '0'))"
    ];
    $contaParams = [];

    if ($dt_ini) {
        $contaWhere[] = "DATE(COALESCE(ca.data_create_cap, ac.data_intern_int)) >= :c_dt_ini";
        $contaParams[':c_dt_ini'] = $dt_ini;
    }
    if ($dt_fim) {
        $contaWhere[] = "DATE(COALESCE(ca.data_create_cap, ac.data_intern_int)) <= :c_dt_fim";
        $contaParams[':c_dt_fim'] = $dt_fim;
    }
    if ($seguradora_id) {
        $contaWhere[] = "pa.fk_seguradora_pac = :c_seguradora_id";
        $contaParams[':c_seguradora_id'] = $seguradora_id;
    }
    if ($responsavel !== '') {
        $contaWhere[] = "ca.usuario_create_cap LIKE :c_resp";
        $contaParams[':c_resp'] = "%" . $responsavel . "%";
    }

    $contaSql = "
        SELECT
            ca.id_capeante,
            ca.lote_cap,
            ca.data_create_cap,
            ca.usuario_create_cap,
            ac.id_internacao,
            pa.nome_pac,
            ho.nome_hosp,
            se.seguradora_seg
        FROM tb_capeante ca
        LEFT JOIN tb_internacao ac ON ac.id_internacao = ca.fk_int_capeante
        LEFT JOIN tb_paciente pa ON pa.id_paciente = ac.fk_paciente_int
        LEFT JOIN tb_hospital ho ON ho.id_hospital = ac.fk_hospital_int
        LEFT JOIN tb_seguradora se ON se.id_seguradora = pa.fk_seguradora_pac
        WHERE " . implode(" AND ", $contaWhere) . "
        ORDER BY ca.data_create_cap DESC
        LIMIT 200
    ";

    $stmt = $conn->prepare($contaSql);
    foreach ($contaParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $contasPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $th) {
    $contasErro = $th->getMessage();
}
?>

<div class="container-fluid" id="main-container" style="margin-top:-5px">
    <h4 class="page-title">Fila de Tarefas</h4>
    <p class="text-muted" style="margin-top:-6px">Visitas e contas pendentes, com filtros por periodo, convenio e responsavel.</p>
    <hr>

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-sm-2">
            <label class="form-label mb-1">Data inicio</label>
            <input type="date" class="form-control form-control-sm" name="dt_ini" value="<?= h($dt_ini) ?>">
        </div>
        <div class="col-sm-2">
            <label class="form-label mb-1">Data fim</label>
            <input type="date" class="form-control form-control-sm" name="dt_fim" value="<?= h($dt_fim) ?>">
        </div>
        <div class="col-sm-4">
            <label class="form-label mb-1">Convenio</label>
            <select class="form-select form-select-sm" name="seguradora_id">
                <option value="">Todos</option>
                <?php foreach ($seguradoras as $seg): ?>
                <option value="<?= (int) $seg['id_seguradora'] ?>" <?= $seguradora_id == $seg['id_seguradora'] ? 'selected' : '' ?>>
                    <?= h($seg['seguradora_seg']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-3">
            <label class="form-label mb-1">Responsavel</label>
            <input type="text" class="form-control form-control-sm" name="responsavel" placeholder="Nome ou email"
                value="<?= h($responsavel) ?>">
        </div>
        <div class="col-sm-1 d-grid">
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        </div>
        <div class="col-sm-1 d-grid">
            <a class="btn btn-outline-secondary btn-sm" href="list_fila_tarefas.php">Limpar</a>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Visitas pendentes</span>
            <span class="badge bg-warning text-dark"><?= count($visitasPendentes) ?></span>
        </div>
        <div class="card-body table-responsive">
            <?php if ($visitasErro): ?>
            <div class="alert alert-warning">Falha ao carregar visitas pendentes. <?= h($visitasErro) ?></div>
            <?php endif; ?>
            <table class="table table-striped table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Internacao</th>
                        <th>Paciente</th>
                        <th>Hospital</th>
                        <th>Convenio</th>
                        <th>Data internacao</th>
                        <th>Responsavel</th>
                        <th class="text-end">Acao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$visitasPendentes): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhuma pendencia encontrada.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($visitasPendentes as $row): ?>
                    <tr>
                        <td><?= h($row['id_internacao']) ?></td>
                        <td><?= h($row['nome_pac']) ?></td>
                        <td><?= h($row['nome_hosp']) ?></td>
                        <td><?= h($row['seguradora_seg'] ?? '-') ?></td>
                        <td><?= h(fmt_date_br($row['data_intern_int'] ?? '')) ?></td>
                        <td><?= h($row['usuario_create_int'] ?? '-') ?></td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm"
                                href="show_internacao.php?id_internacao=<?= h($row['id_internacao']) ?>">Abrir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Contas pendentes</span>
            <span class="badge bg-warning text-dark"><?= count($contasPendentes) ?></span>
        </div>
        <div class="card-body table-responsive">
            <?php if ($contasErro): ?>
            <div class="alert alert-warning">Falha ao carregar contas pendentes. <?= h($contasErro) ?></div>
            <?php endif; ?>
            <table class="table table-striped table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Conta</th>
                        <th>Internacao</th>
                        <th>Paciente</th>
                        <th>Hospital</th>
                        <th>Convenio</th>
                        <th>Data criacao</th>
                        <th>Responsavel</th>
                        <th class="text-end">Acao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$contasPendentes): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Nenhuma pendencia encontrada.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($contasPendentes as $row): ?>
                    <tr>
                        <td><?= h($row['id_capeante']) ?></td>
                        <td><?= h($row['id_internacao'] ?? '-') ?></td>
                        <td><?= h($row['nome_pac']) ?></td>
                        <td><?= h($row['nome_hosp']) ?></td>
                        <td><?= h($row['seguradora_seg'] ?? '-') ?></td>
                        <td><?= h(fmt_date_br($row['data_create_cap'] ?? '')) ?></td>
                        <td><?= h($row['usuario_create_cap'] ?? '-') ?></td>
                        <td class="text-end">
                            <a class="btn btn-outline-primary btn-sm"
                                href="show_capeante.php?id_capeante=<?= h($row['id_capeante']) ?>">Abrir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
