<?php
/*──────────────────────────────────────────────────────
  process_internacao_editar.php – fluxo UPDATE / CREATE
────────────────────────────────────────────────────────*/
require_once 'globals.php';
require_once 'db.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Models & DAOs */
require_once 'models/internacao.php';
require_once 'dao/internacaoDao.php';
require_once 'models/detalhes.php';
require_once 'dao/detalhesDao.php';
require_once 'models/uti.php';
require_once 'dao/utiDao.php';
require_once 'models/negociacao.php';
require_once 'dao/negociacaoDao.php';
require_once 'models/prorrogacao.php';
require_once 'dao/prorrogacaoDao.php';
require_once 'models/tuss.php';
require_once 'dao/tussDao.php';
require_once 'models/gestao.php';
require_once 'dao/gestaoDao.php';


/*────────────────── SESSION ─────────────────*/
$idInternacao = (int) ($_POST['id_internacao'] ?? 0);
if (!$idInternacao) {
    die("ID de internação ausente");
}


function limpa(?string $t, int $lim = 5000): string
{
    $t = htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8');
    $t = preg_replace('/[^\wÀ-ÖØ-öø-ÿ .,!?:()\-]/u', '', $t);
    return substr($t, 0, $lim);
}

$internacaoDao = new InternacaoDAO($conn, $BASE_URL);
$detalhesDao = new detalhesDAO($conn, $BASE_URL);
$utiDao = new utiDAO($conn, $BASE_URL);
$negDao = new negociacaoDAO($conn, $BASE_URL);
$prorrogDao = new prorrogacaoDAO($conn, $BASE_URL);
$tussDao = new tussDAO($conn, $BASE_URL);
$gestaoDao = new gestaoDAO($conn, $BASE_URL);

$type = filter_input(INPUT_POST, 'type');
$idInt = filter_input(INPUT_POST, 'id_internacao', FILTER_VALIDATE_INT);

// if ($type !== 'update_editar' || !$idInt) {
//     header('Location:list_internacao.php');
//     exit;
// }

/*────────────────── INTERNACAO ─────────────────*/
$int = new internacao();
$int->id_internacao = $idInt;
$int->fk_hospital_int = filter_input(INPUT_POST, 'fk_hospital_int', FILTER_VALIDATE_INT);
$int->fk_paciente_int = filter_input(INPUT_POST, 'fk_paciente_int', FILTER_VALIDATE_INT);
$int->fk_patologia_int = filter_input(INPUT_POST, 'fk_patologia_int', FILTER_VALIDATE_INT) ?: 1;
$int->fk_patologia2 = filter_input(INPUT_POST, 'fk_patologia2', FILTER_VALIDATE_INT) ?: 1;
$int->internado_int = filter_input(INPUT_POST, 'internado_int');
$int->modo_internacao_int = filter_input(INPUT_POST, 'modo_internacao_int');
$int->tipo_admissao_int = filter_input(INPUT_POST, 'tipo_admissao_int');
$int->grupo_patologia_int = filter_input(INPUT_POST, 'grupo_patologia_int');
$int->data_visita_int = filter_input(INPUT_POST, 'data_visita_int') ?: null;
$int->data_intern_int = filter_input(INPUT_POST, 'data_intern_int') ?: null;
$int->especialidade_int = filter_input(INPUT_POST, 'especialidade_int');
$int->titular_int = filter_input(INPUT_POST, 'titular_int');
$int->crm_int = filter_input(INPUT_POST, 'crm_int');
$int->acomodacao_int = filter_input(INPUT_POST, 'acomodacao_int');
$int->rel_int = limpa(filter_input(INPUT_POST, 'rel_int'));
$int->acoes_int = limpa(filter_input(INPUT_POST, 'acoes_int'));
$int->programacao_int = limpa(filter_input(INPUT_POST, 'programacao_int'));
$int->usuario_create_int = $_SESSION['id_usuario'] ?? null;
$int->num_atendimento_int = filter_input(INPUT_POST, 'num_atendimento_int', FILTER_VALIDATE_INT) ?: null;

$internacaoDao->update($int);

/*────────────────── DETALHES ─────────────────*/
if (filter_input(INPUT_POST, 'select_detalhes') === 's') {
    $fkVis = filter_input(INPUT_POST, 'fk_vis_det', FILTER_VALIDATE_INT);
    $idDetalhes = filter_input(INPUT_POST, 'id_detalhes', FILTER_VALIDATE_INT);

    $d = new detalhes();
    $d->id_detalhes = $idDetalhes;
    $d->fk_int_det = $idInt;
    $d->fk_vis_det = $fkVis;
    $d->curativo_det = filter_input(INPUT_POST, 'curativo_det');
    $d->dieta_det = filter_input(INPUT_POST, 'dieta_det');
    $d->nivel_consc_det = filter_input(INPUT_POST, 'nivel_consc_det');
    $d->oxig_det = filter_input(INPUT_POST, 'oxig_det');
    $d->oxig_uso_det = filter_input(INPUT_POST, 'oxig_uso_det');
    $d->qt_det = filter_input(INPUT_POST, 'qt_det');
    $d->dispositivo_det = filter_input(INPUT_POST, 'dispositivo_det');
    $d->atb_det = filter_input(INPUT_POST, 'atb_det');
    $d->atb_uso_det = filter_input(INPUT_POST, 'atb_uso_det');
    $d->acamado_det = filter_input(INPUT_POST, 'acamado_det');
    $d->exames_det = limpa(filter_input(INPUT_POST, 'exames_det'));
    $d->oportunidades_det = limpa(filter_input(INPUT_POST, 'oportunidades_det'));
    $d->tqt_det = filter_input(INPUT_POST, 'tqt_det');
    $d->svd_det = filter_input(INPUT_POST, 'svd_det');
    $d->gtt_det = filter_input(INPUT_POST, 'gtt_det');
    $d->dreno_det = filter_input(INPUT_POST, 'dreno_det');
    $d->rt_det = filter_input(INPUT_POST, 'rt_det');
    $d->lesoes_pele_det = filter_input(INPUT_POST, 'lesoes_pele_det');
    $d->medic_alto_custo_det = filter_input(INPUT_POST, 'medic_alto_custo_det');
    $d->qual_medicamento_det = filter_input(INPUT_POST, 'qual_medicamento_det');
    $d->hemoderivados_det = filter_input(INPUT_POST, 'hemoderivados_det');
    $d->dialise_det = filter_input(INPUT_POST, 'dialise_det');
    $d->oxigenio_hiperbarica_det = filter_input(INPUT_POST, 'oxigenio_hiperbarica_det');
    $d->paliativos_det = filter_input(INPUT_POST, 'paliativos_det');
    $d->braden_det = filter_input(INPUT_POST, 'braden_det');
    $d->liminar_det = filter_input(INPUT_POST, 'liminar_det');
    $d->parto_det = filter_input(INPUT_POST, 'parto_det');
    error_log('[detalhes] Dados recebidos: ' . print_r($d, true));
    try {
        if (!$idDetalhes) {
            unset($d->id_detalhes);
            error_log('[detalhes] Criando novo registro de detalhes');
            $detalhesDao->create($d);
        } else {
            error_log('[detalhes] Atualizando registro de detalhes com ID: ' . $idDetalhes);
            $detalhesDao->update($d);
        }
    } catch (Throwable $e) {
        error_log('[detalhes] ' . $e->getMessage());
    }
}


/*────────────────── UTI (CREATE) ─────────────────*/
if (filter_input(INPUT_POST, 'select_uti') === 's') {
    error_log("DADOS RECEBIDOS EM UTI:\n" . print_r($_POST, true));

    $u = new uti();

    $u->id_uti = filter_input(INPUT_POST, 'id_uti', FILTER_VALIDATE_INT);
    $u->fk_internacao_uti = filter_input(INPUT_POST, 'fk_internacao_uti', FILTER_VALIDATE_INT);
    $u->hora_internacao_uti = filter_input(INPUT_POST, 'hora_internacao_uti');
    $u->data_internacao_uti = filter_input(INPUT_POST, 'data_internacao_uti');
    $u->vm_uti = filter_input(INPUT_POST, 'vm_uti');
    $u->dva_uti = filter_input(INPUT_POST, 'dva_uti');
    $u->motivo_uti = filter_input(INPUT_POST, 'motivo_uti');
    $u->rel_uti = limpa(filter_input(INPUT_POST, 'rel_uti'));
    $u->just_uti = filter_input(INPUT_POST, 'just_uti');
    $u->saps_uti = filter_input(INPUT_POST, 'saps_uti');
    $u->score_uti = filter_input(INPUT_POST, 'score_uti');         // estava faltando
    $u->criterios_uti = filter_input(INPUT_POST, 'criterio_uti');      // estava faltando
    $u->internado_uti = filter_input(INPUT_POST, 'internado_uti');     // estava faltando
    if (!empty($u->id_uti)) {
        $utiDao->update($u);
    } else {
        $utiDao->create($u);
    }
}

/*────────────────── GESTAO (CREATE) ─────────────────*/
if (filter_input(INPUT_POST, 'select_gestao') === 's') {
    // tente obter um ID de gestão existente (se for editar)
    $idGestao = filter_input(INPUT_POST, 'id_gestao', FILTER_VALIDATE_INT);

    $gestao = new gestao();

    // se houver ID, atribua para que o DAO faça o UPDATE
    if ($idGestao) {
        $gestao->id_gestao = $idGestao;
    }

    // FK internacao e visita
    $gestao->fk_internacao_ges = $idInt;
    $gestao->fk_visita_ges = filter_input(INPUT_POST, 'fk_visita_ges', FILTER_VALIDATE_INT);

    // Campos binários/texto
    $gestao->alto_custo_ges = filter_input(INPUT_POST, 'alto_custo_ges');
    $gestao->rel_alto_custo_ges = limpa(filter_input(INPUT_POST, 'rel_alto_custo_ges'));
    $gestao->opme_ges = filter_input(INPUT_POST, 'opme_ges');
    $gestao->rel_opme_ges = limpa(filter_input(INPUT_POST, 'rel_opme_ges'));
    $gestao->home_care_ges = filter_input(INPUT_POST, 'home_care_ges');
    $gestao->rel_home_care_ges = limpa(filter_input(INPUT_POST, 'rel_home_care_ges'));
    $gestao->desospitalizacao_ges = filter_input(INPUT_POST, 'desospitalizacao_ges');
    $gestao->rel_desospitalizacao_ges = limpa(filter_input(INPUT_POST, 'rel_desospitalizacao_ges'));
    $gestao->evento_adverso_ges = filter_input(INPUT_POST, 'evento_adverso_ges');
    $gestao->rel_evento_adverso_ges = limpa(filter_input(INPUT_POST, 'rel_evento_adverso_ges'));
    $gestao->tipo_evento_adverso_gest = filter_input(INPUT_POST, 'tipo_evento_adverso_gest');
    $gestao->evento_sinalizado_ges = filter_input(INPUT_POST, 'evento_sinalizado_ges');
    $gestao->evento_discutido_ges = filter_input(INPUT_POST, 'evento_discutido_ges');
    $gestao->evento_negociado_ges = filter_input(INPUT_POST, 'evento_negociado_ges');
    $gestao->evento_prorrogar_ges = filter_input(INPUT_POST, 'evento_prorrogar_ges');
    $gestao->evento_fech_ges = filter_input(INPUT_POST, 'evento_fech_ges');
    $gestao->evento_valor_negoc_ges = filter_input(INPUT_POST, 'evento_valor_negoc_ges');
    $gestao->evento_retorno_qual_hosp_ges = filter_input(INPUT_POST, 'evento_retorno_qual_hosp_ges');
    $gestao->evento_classificado_hospital_ges = filter_input(INPUT_POST, 'evento_classificado_hospital_ges');
    $gestao->evento_data_ges = filter_input(INPUT_POST, 'evento_data_ges') ?: null;
    $gestao->evento_encerrar_ges = filter_input(INPUT_POST, 'evento_encerrar_ges');
    $gestao->evento_impacto_financ_ges = filter_input(INPUT_POST, 'evento_impacto_financ_ges');
    $gestao->evento_prolongou_internacao_ges = filter_input(INPUT_POST, 'evento_prolongou_internacao_ges');
    $gestao->evento_concluido_ges = filter_input(INPUT_POST, 'evento_concluido_ges');
    $gestao->evento_classificacao_ges = filter_input(INPUT_POST, 'evento_classificacao_ges');
    $gestao->evento_fech_ges = filter_input(INPUT_POST, 'evento_fech_ges');

    // usuário que está editando (pode usar sessão)
    $gestao->fk_user_ges = filter_input(INPUT_POST, 'fk_user_ges', FILTER_VALIDATE_INT)
        ?? ($_SESSION['id_usuario'] ?? null);
    if ($idGestao) {
        // UPDATE
        $gestaoDao->update($gestao);
    } else {
        // CREATE
        $gestaoDao->create($gestao);
    }
}


/*────────────────── NEGOCIAÇÕES (UPDATE/CREATE/DELETE) ─────────────────*/
if (filter_input(INPUT_POST, 'select_negoc') === 's') {

    $existing = $negDao->findByInternacao($idInt);
    // agora usamos a chave de array
    $existingIds = array_map(fn(array $r) => (int) $r['id_negociacao'], $existing);

    // 2) decodifica o JSON
    $negArray = json_decode($_POST['negociacoes_json'] ?? '[]', true);

    // 3) extrai IDs postados   
    $postedIds = [];
    foreach ($negArray as $n) {
        if (!empty($n['id'])) {
            $postedIds[] = (int) $n['id'];
        }
    }

    // 4) deleta os removidos
    $toDelete = array_diff($existingIds, $postedIds);
    foreach ($toDelete as $delId) {
        $negDao->destroy($delId);
        error_log("[NEGOCIAÇÃO] Deletada ID $delId");
    }

    // 5) update/create
    foreach ($negArray as $nData) {
        $neg = new negociacao();
        if (!empty($nData['id'])) {
            $neg->id_negociacao = (int) $nData['id'];
        }
        $neg->fk_id_int = $idInt;
        $neg->troca_de = $nData['troca_de'];
        $neg->troca_para = $nData['troca_para'];
        $neg->qtd = (int) $nData['qtd'];
        $neg->saving = (float) $nData['saving'];

        $neg->data_inicio_neg = $nData['data_inicio_neg'];
        $neg->data_fim_neg = $nData['data_fim_neg'];

        $neg->tipo_negociacao = $nData['tipo_negociacao'];

        if (!empty($neg->id_negociacao)) {
            $negDao->update($neg);
        } else {
            $negDao->create($neg);
        }
    }
}




/*────────────────── PRORROGAÇÕES (UPDATE/CREATE/DELETE) ─────────────────*/
if (filter_input(INPUT_POST, 'select_prorrog') === 's') {
    // 1) pega todos os IDs atuais de prorrogação
    $existing = $prorrogDao->selectInternacaoProrrog($idInt);
    $existingIds = array_map(fn($r) => (int) $r['id_prorrogacao'], $existing);
    // 2) decodifica o JSON
    $prArray = json_decode($_POST['prorrogacoes_json'] ?? '[]', true);

    // 3) extrai os IDs postados
    $postedIds = [];
    foreach ($prArray as $p) {
        if (!empty($p['id_prorrogacao'])) {
            $postedIds[] = (int) $p['id_prorrogacao'];
        }
    }

    // 4) deleta o que foi removido no form
    $toDelete = array_diff($existingIds, $postedIds);
    foreach ($toDelete as $delId) {
        $prorrogDao->destroy($delId);
        error_log("[PRORROGAÇÃO] Deletada ID $delId");
    }

    // 5) update/create
    foreach ($prArray as $p) {
        $pr = new prorrogacao();
        if (!empty($p['id_prorrogacao'])) {
            $pr->id_prorrogacao = (int) $p['id_prorrogacao'];
        }
        $pr->fk_internacao_pror = $idInt;
        $pr->acomod1_pror = $p['acomod'];
        $pr->isol_1_pror = $p['isolamento'];
        $pr->prorrog1_ini_pror = $p['ini'] ?: null;
        $pr->prorrog1_fim_pror = $p['fim'] ?: null;
        $pr->diarias_1 = (int) ($p['diarias'] ?? 0);

        if (!empty($pr->id_prorrogacao)) {
            $prorrogDao->update($pr);
        } else {
            $prorrogDao->create($pr);
        }
    }
}


function montarTussFromJson(array $item, int $idInternacao, int $idUsuario): tuss
{
    $tuss = new tuss();
    // CAST seguro para inteiros
    $tuss->id_tuss = !empty($item['id_tuss']) ? (int) $item['id_tuss'] : null;
    $tuss->fk_int_tuss = !empty($item['fk_int_tuss']) ? (int) $item['fk_int_tuss'] : $idInternacao;

    // $tuss->id_tuss              = $item['id_tuss'] ?? null;
    // $tuss->fk_int_tuss          = $fk_int_tuss ??  null;
    $tuss->tuss_solicitado = $item['tuss_solicitado'] ?? '';
    $tuss->tuss_liberado_sn = $item['tuss_liberado_sn'] ?? '';
    $tuss->qtd_tuss_solicitado = $item['qtd_tuss_solicitado'] ?? '';
    $tuss->qtd_tuss_liberado = $item['qtd_tuss_liberado'] ?? '';
    $tuss->data_realizacao_tuss = $item['data_realizacao_tuss'] ?? null;

    $tuss->fk_vis_tuss = $item['fk_vis_tuss'] ?? null;
    $tuss->fk_usuario_tuss = $idUsuario;
    $tuss->data_create_tuss = date('Y-m-d H:i:s');
    $tuss->glosa_tuss = null;

    return $tuss;
}

/*────────────────── TUSS (CREATE / UPDATE / DESTROY) ─────────────────*/
if (filter_input(INPUT_POST, 'select_tuss') === 's') {

    $tussJson = json_decode($_POST['tuss_json'], true);
    $tussDao = new tussDAO($conn, $BASE_URL);
    $idInternacao = (int) $_POST['id_internacao'];
    $idUsuario = (int) $_SESSION['id_usuario'];

    // 1) buscar todos os tuss existentes para esta internação
    $existentes = $tussDao->findByIdIntern($idInternacao); // ou selectTUSSByIntern
    $existIds = array_map(fn($r) => (int) ($r['id_tuss'] ?? $r->id_tuss), $existentes);

    // 2) coletar os IDs que vieram no form
    $incomingIds = [];
    foreach ($tussJson as $item) {
        if (!empty($item['id_tuss'])) {
            $incomingIds[] = (int) $item['id_tuss'];
        }
    }

    // 3) qualquer ID existente que NÃO esteja em $incomingIds deve ser deletado
    $toDelete = array_diff($existIds, $incomingIds);
    foreach ($toDelete as $delId) {
        $tussDao->destroy($delId);
    }

    // 4) agora o loop normal de create / update
    foreach ($tussJson as $item) {
        // pula linhas em branco
        if (empty($item['tuss_solicitado']))
            continue;

        $tuss = montarTussFromJson($item, $idInternacao, $idUsuario);

        if (!empty($item['id_tuss'])) {
            // se veio id, era um registro existente
            $tussDao->update($tuss);
        } else {
            // novo registro
            $tussDao->create($tuss);
        }
    }

    // 5) redireciona
    header('Location: list_internacao.php');
}