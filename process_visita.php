<?php
// ======================================================================
// process_visita.php  (refatorado, sem alterar métodos existentes)
// ======================================================================

// --- Debug opcional na tela: use ?debug=1 para ver erros/prints ---
$__DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($__DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
function dbg(...$args)
{
    global $__DEBUG;
    if (!$__DEBUG) return;
    echo "<pre style='background:#111;color:#0f0;padding:10px;border-radius:6px;line-height:1.25;'>"
        . htmlspecialchars(print_r(count($args) === 1 ? $args[0] : $args, true), ENT_QUOTES, 'UTF-8')
        . "</pre>";
}

// ======================================================================
// Includes / DAOs / Models
// ======================================================================
require_once("globals.php");
require_once("db.php");

require_once("models/internacao.php");
require_once("dao/internacaoDao.php");

require_once("models/gestao.php");
require_once("dao/gestaoDao.php");

require_once("models/tuss.php");
require_once("dao/tussDao.php");

require_once("models/uti.php");
require_once("dao/utiDao.php");

require_once("models/negociacao.php");
require_once("dao/negociacaoDao.php");

require_once("models/visita.php");
require_once("dao/visitaDao.php");

require_once("models/prorrogacao.php");
require_once("dao/prorrogacaoDao.php");

require_once("models/internacao_antecedente.php");
require_once("dao/internacaoAntecedenteDao.php");

require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

require_once("models/message.php");

$message                = new Message($BASE_URL);
$userDao                = new UserDAO($conn, $BASE_URL);
$internacaoDao          = new internacaoDAO($conn, $BASE_URL);
$gestaoDao              = new gestaoDAO($conn, $BASE_URL);
$utiDao                 = new utiDAO($conn, $BASE_URL);
$negociacaoDao          = new negociacaoDAO($conn, $BASE_URL);
$tussDao                = new tussDAO($conn, $BASE_URL);
$prorrogacaoDao         = new prorrogacaoDAO($conn, $BASE_URL);
$visitaDao              = new visitaDAO($conn, $BASE_URL);
$internAntecedenteDao   = new InternacaoAntecedenteDAO($conn, $BASE_URL);

// ======================================================================
// Utilitários simples
// ======================================================================
function toIntOrNull($v)
{
    if ($v === null || $v === '') return null;
    $iv = filter_var($v, FILTER_VALIDATE_INT);
    return ($iv === false) ? null : $iv;
}
function toFloatOrNull($v)
{
    if ($v === null || $v === '') return null;
    $fv = filter_var(str_replace(',', '.', $v), FILTER_VALIDATE_FLOAT);
    return ($fv === false) ? null : $fv;
}
function strOrNull($v)
{
    $v = is_string($v) ? trim($v) : '';
    return $v === '' ? null : $v;
}

// ======================================================================
// Roteamento por tipo
// ======================================================================
$type = filter_input(INPUT_POST, "type"); // create | update | delete (delete pode vir por GET no seu fluxo)

// ----------------------------------------------------------------------
// CREATE
// ----------------------------------------------------------------------
if ($type === "create") {

    // ------------------- Campos principais da visita -------------------
    $fk_internacao_vis           = toIntOrNull($_POST['fk_internacao_vis'] ?? null);
    $usuario_create              = strOrNull($_POST['usuario_create'] ?? null);
    $rel_visita_vis              = strOrNull($_POST['rel_visita_vis'] ?? null);
    $acoes_int_vis               = strOrNull($_POST['acoes_int_vis'] ?? null);
    $data_visita_vis             = strOrNull($_POST['data_visita_vis'] ?? null);
    $visita_no_vis               = toIntOrNull($_POST['visita_no_vis'] ?? null);
    $visita_enf_vis              = strOrNull($_POST['visita_enf_vis'] ?? null);
    $visita_med_vis              = strOrNull($_POST['visita_med_vis'] ?? null);
    $visita_auditor_prof_enf     = strOrNull($_POST['visita_auditor_prof_enf'] ?? null);
    $visita_auditor_prof_med     = strOrNull($_POST['visita_auditor_prof_med'] ?? null);
    $fk_usuario_vis              = toIntOrNull($_POST['fk_usuario_vis'] ?? null);

    // bloco enfermagem (visita)
    $exames_enf                  = strOrNull($_POST['exames_enf'] ?? null);
    $oportunidades_enf           = strOrNull($_POST['oportunidades_enf'] ?? null);
    $programacao_enf             = strOrNull($_POST['programacao_enf'] ?? null);

    // retificar (ATENÇÃO: precisa ser número de visita, não data)
    $retificou                   = toIntOrNull($_POST['retificou'] ?? null);

    // json antecedentes
    $jsonAntecRaw                = $_POST['json-antec'] ?? null;

    // ------------------- Tabelas adicionais (flags) --------------------
    $select_tuss                 = strOrNull($_POST['select_tuss'] ?? null);     // 's'/'n'
    $select_gestao               = strOrNull($_POST['select_gestao'] ?? null);   // 's'/'n'
    $select_uti                  = strOrNull($_POST['select_uti'] ?? null);      // 's'/'n'
    $select_prorrog              = strOrNull($_POST['select_prorrog'] ?? null);  // 's'/'n'
    $select_negoc                = strOrNull($_POST['select_negoc'] ?? null);    // 's'/'n'

    // ------------------- IDs auxiliares usados por você ----------------
    $fk_int_visita               = toIntOrNull($_POST['fk_int_visita'] ?? null); // você já envia "próximo id" no form

    // ------------------- Sanidade mínima ------------------------------
    if (!$fk_internacao_vis) {
        if ($__DEBUG) {
            dbg("ERRO: fk_internacao_vis ausente ou inválido");
            exit;
        }
        $message->setMessage("Informações inválidas da internação.", "error", "back");
        exit;
    }
    if (!$data_visita_vis) {
        if ($__DEBUG) {
            dbg("ERRO: data_visita_vis vazia");
            exit;
        }
        $message->setMessage("Data da visita é obrigatória.", "error", "back");
        exit;
    }

    // ------------------- Retificação (somente se inteiro) --------------
    try {
        if (is_int($retificou) && $retificou > 0) {
            // Assinatura esperada: retificarVisita(int $fk_internacao, int $visita_no_vis)
            $visitaDao->retificarVisita((int)$fk_internacao_vis, (int)$retificou);
            if ($__DEBUG) dbg("Retificação aplicada para visita_no_vis", $retificou);
        }
    } catch (Throwable $e) {
        error_log("retificarVisita falhou: " . $e->getMessage());
        if ($__DEBUG) dbg("retificarVisita EXCEPTION", $e->getMessage());
        // Não aborta o fluxo, apenas registra.
    }

    // ------------------- Monta objeto VISITA ---------------------------
    $visita                           = new visita();
    $visita->fk_internacao_vis        = $fk_internacao_vis;
    $visita->usuario_create           = $usuario_create;
    $visita->rel_visita_vis           = $rel_visita_vis;
    $visita->acoes_int_vis            = $acoes_int_vis;
    $visita->data_visita_vis          = $data_visita_vis;
    $visita->visita_no_vis            = $visita_no_vis;
    $visita->visita_enf_vis           = $visita_enf_vis;
    $visita->visita_med_vis           = $visita_med_vis;
    $visita->visita_auditor_prof_enf  = $visita_auditor_prof_enf;
    $visita->visita_auditor_prof_med  = $visita_auditor_prof_med;
    $visita->fk_usuario_vis           = $fk_usuario_vis;

    // enfermagem (texto)
    $visita->exames_enf               = $exames_enf;
    $visita->oportunidades_enf        = $oportunidades_enf;
    $visita->programacao_enf          = $programacao_enf;

    // ------------------- Persistência VISITA --------------------------
    try {
        $visitaDao->create($visita);
        if ($__DEBUG) dbg("VISITA criada", $visita);
    } catch (Throwable $e) {
        error_log("Erro ao criar visita: " . $e->getMessage());
        if ($__DEBUG) {
            dbg("ERRO create visita", $e->getMessage());
            exit;
        }
        $message->setMessage("Erro ao salvar visita.", "error", "back");
        exit;
    }

    // ------------------- Antecedentes (JSON) --------------------------
    if ($jsonAntecRaw) {
        $antecArr = json_decode($jsonAntecRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Antecedentes JSON inválido: " . json_last_error_msg());
            if ($__DEBUG) dbg("JSON Antec inválido", json_last_error_msg(), $jsonAntecRaw);
        } elseif (is_array($antecArr)) {
            foreach ($antecArr as $row) {
                try {
                    // O seu DAO possui buildintern_antec($data)
                    $intern_antec = $internAntecedenteDao->buildintern_antec($row);
                    $internAntecedenteDao->create($intern_antec);
                } catch (Throwable $e) {
                    error_log("Erro ao salvar antecedente: " . $e->getMessage());
                    if ($__DEBUG) dbg("Antecedente erro", $e->getMessage(), $row);
                }
            }
        }
    }

    // ------------------- GESTAO --------------------------
    if ($select_gestao === 's') {
        try {
            $gestao = new gestao();

            $gestao->fk_internacao_ges             = $fk_internacao_vis;
            $gestao->fk_visita_ges                 = toIntOrNull($_POST['fk_visita_ges'] ?? null);
            $gestao->alto_custo_ges                = strOrNull($_POST['alto_custo_ges'] ?? null);
            $gestao->rel_alto_custo_ges            = strOrNull($_POST['rel_alto_custo_ges'] ?? null);
            $gestao->opme_ges                      = strOrNull($_POST['opme_ges'] ?? null);
            $gestao->rel_opme_ges                  = strOrNull($_POST['rel_opme_ges'] ?? null);
            $gestao->home_care_ges                 = strOrNull($_POST['home_care_ges'] ?? null);
            $gestao->rel_home_care_ges             = strOrNull($_POST['rel_home_care_ges'] ?? null);
            $gestao->desospitalizacao_ges          = strOrNull($_POST['desospitalizacao_ges'] ?? null);
            $gestao->rel_desospitalizacao_ges      = strOrNull($_POST['rel_desospitalizacao_ges'] ?? null);

            $gestao->evento_adverso_ges            = strOrNull($_POST['evento_adverso_ges'] ?? null);
            $gestao->rel_evento_adverso_ges        = strOrNull($_POST['rel_evento_adverso_ges'] ?? null);
            $gestao->tipo_evento_adverso_gest      = strOrNull($_POST['tipo_evento_adverso_gest'] ?? null);
            $gestao->evento_retorno_qual_hosp_ges  = strOrNull($_POST['evento_retorno_qual_hosp_ges'] ?? null);
            $gestao->evento_classificado_hospital_ges = strOrNull($_POST['evento_classificado_hospital_ges'] ?? null);
            $gestao->evento_data_ges               = strOrNull($_POST['evento_data_ges'] ?? null);
            $gestao->evento_encerrar_ges           = strOrNull($_POST['evento_encerrar_ges'] ?? null);
            $gestao->evento_impacto_financ_ges     = strOrNull($_POST['evento_impacto_financ_ges'] ?? null);
            $gestao->evento_prolongou_internacao_ges = strOrNull($_POST['evento_prolongou_internacao_ges'] ?? null);
            $gestao->evento_concluido_ges          = strOrNull($_POST['evento_concluido_ges'] ?? null);
            $gestao->evento_classificacao_ges      = strOrNull($_POST['evento_classificacao_ges'] ?? null);
            $gestao->evento_fech_ges               = strOrNull($_POST['evento_fech_ges'] ?? null);
            $gestao->fk_user_ges                   = toIntOrNull($_POST['fk_user_ges'] ?? null);

            $gestaoDao->create($gestao);
            if ($__DEBUG) dbg("GESTAO criada", $gestao);
        } catch (Throwable $e) {
            error_log("Erro ao criar gestão: " . $e->getMessage());
            if ($__DEBUG) dbg("ERRO create gestao", $e->getMessage());
        }
    }

    // ------------------- TUSS (JSON) --------------------
    if ($select_tuss === 's') {
        $tussJson = $_POST['tuss-json'] ?? '[]';
        $tussArr = json_decode($tussJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("TUSS JSON inválido: " . json_last_error_msg());
            if ($__DEBUG) dbg("JSON TUSS inválido", json_last_error_msg(), $tussJson);
        } elseif (is_array($tussArr) && isset($tussArr['tussEntries']) && is_array($tussArr['tussEntries'])) {
            foreach ($tussArr['tussEntries'] as $row) {
                try {
                    $tuss = new tuss();
                    $tuss->fk_int_tuss           = $fk_internacao_vis;
                    $tuss->fk_usuario_tuss       = toIntOrNull($row['fk_usuario_tuss'] ?? null);
                    $tuss->tuss_solicitado       = strOrNull($row['tuss_solicitado'] ?? null);
                    $tuss->data_realizacao_tuss  = strOrNull($row['data_realizacao_tuss'] ?? null);
                    $tuss->qtd_tuss_solicitado   = toIntOrNull($row['qtd_tuss_solicitado'] ?? null);
                    $tuss->qtd_tuss_liberado     = toIntOrNull($row['qtd_tuss_liberado'] ?? null);
                    $tuss->tuss_liberado_sn      = strOrNull($row['tuss_liberado_sn'] ?? null);
                    // Você usava isso assim no arquivo original:
                    $tuss->fk_vis_tuss           = $row['fk_int_tuss'] ?? null; // mantido
                    $tuss->data_create_tuss      = $data_visita_vis;

                    $tussDao->create($tuss);
                } catch (Throwable $e) {
                    error_log("Erro ao criar TUSS: " . $e->getMessage());
                    if ($__DEBUG) dbg("ERRO create TUSS", $e->getMessage(), $row);
                }
            }
        }
    }

    // ------------------- UTI ----------------------------
    if ($select_uti === 's') {
        try {
            $uti = new uti();

            $uti->fk_internacao_uti    = $fk_internacao_vis;
            $uti->internado_uti        = strOrNull($_POST['internado_uti'] ?? null);
            $uti->criterios_uti        = strOrNull($_POST['criterios_uti'] ?? null);
            $uti->data_alta_uti        = strOrNull($_POST['data_alta_uti'] ?? null);
            $uti->data_internacao_uti  = strOrNull($_POST['data_internacao_uti'] ?? null);
            $uti->dva_uti              = strOrNull($_POST['dva_uti'] ?? null);
            $uti->especialidade_uti    = strOrNull($_POST['especialidade_uti'] ?? null);
            $uti->internacao_uti       = strOrNull($_POST['internacao_uti'] ?? null);
            $uti->just_uti             = strOrNull($_POST['just_uti'] ?? null);
            $uti->motivo_uti           = strOrNull($_POST['motivo_uti'] ?? null);
            $uti->rel_uti              = strOrNull($_POST['rel_uti'] ?? null);
            $uti->saps_uti             = strOrNull($_POST['saps_uti'] ?? null);
            $uti->score_uti            = strOrNull($_POST['score_uti'] ?? null);
            $uti->vm_uti               = strOrNull($_POST['vm_uti'] ?? null);
            $uti->id_internacao        = toIntOrNull($_POST['id_internacao'] ?? null);
            $uti->usuario_create_uti   = $usuario_create;
            $uti->fk_user_uti          = toIntOrNull($_POST['fk_user_uti'] ?? null);
            $uti->glasgow_uti          = strOrNull($_POST['glasgow_uti'] ?? null);
            $uti->suporte_vent_uti     = strOrNull($_POST['suporte_vent_uti'] ?? null);
            $uti->justifique_uti       = strOrNull($_POST['justifique_uti'] ?? null);
            $uti->hora_internacao_uti  = strOrNull($_POST['hora_internacao_uti'] ?? null);
            $uti->dist_met_uti         = strOrNull($_POST['dist_met_uti'] ?? null);
            $uti->fk_visita_uti        = $fk_int_visita; // você já envia via hidden

            $utiDao->create($uti);
            if ($__DEBUG) dbg("UTI criada", $uti);
        } catch (Throwable $e) {
            error_log("Erro ao criar UTI: " . $e->getMessage());
            if ($__DEBUG) dbg("ERRO create UTI", $e->getMessage());
        }
    }

    // ------------------- NEGOCIAÇÕES (JSON) -------------
    if ($select_negoc === 's') {
        $negJson = $_POST['negociacoes_json'] ?? '[]';
        $negArr  = json_decode($negJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Negociações JSON inválido: " . json_last_error_msg());
            if ($__DEBUG) dbg("JSON Neg inválido", json_last_error_msg(), $negJson);
        } elseif (is_array($negArr) && count($negArr) > 0) {
            foreach ($negArr as $row) {
                try {
                    $negociacao                     = new Negociacao();
                    $negociacao->fk_id_int          = $fk_internacao_vis;
                    $negociacao->fk_usuario_neg     = toIntOrNull($row['fk_usuario_neg'] ?? null);
                    $negociacao->troca_de           = toIntOrNull($row['troca_de'] ?? null);
                    $negociacao->troca_para         = toIntOrNull($row['troca_para'] ?? null);
                    $negociacao->qtd                = toIntOrNull($row['qtd'] ?? null);
                    $negociacao->saving             = toFloatOrNull($row['saving'] ?? null);
                    $negociacao->fk_visita_neg      = $fk_int_visita;

                    $negociacao->tipo_negociacao    = strOrNull($row['tipo_negociacao'] ?? null);
                    $negociacao->data_inicio_neg    = strOrNull($row['data_inicio_negoc'] ?? null);
                    $negociacao->data_fim_neg       = strOrNull($row['data_fim_negoc'] ?? null);

                    // valida mínimo
                    if (!$negociacao->troca_de || !$negociacao->troca_para || !$negociacao->qtd || $negociacao->saving === null) {
                        if ($__DEBUG) dbg("NEG inválida ignorada", $row);
                        continue;
                    }

                    if (!$negociacaoDao->existeNegociacao($negociacao)) {
                        $negociacaoDao->create($negociacao);
                    } else {
                        if ($__DEBUG) dbg("NEG duplicada ignorada", $negociacao);
                    }
                } catch (Throwable $e) {
                    error_log("Erro ao criar negociação: " . $e->getMessage());
                    if ($__DEBUG) dbg("ERRO create NEG", $e->getMessage(), $row);
                }
            }
        }
    }

    // ------------------- PRORROGAÇÕES (JSON) ------------
    if ($select_prorrog === 's') {
        $prJson = $_POST['prorrogacoes-json'] ?? '[]';
        $prArr  = json_decode($prJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Prorrogações JSON inválido: " . json_last_error_msg());
            if ($__DEBUG) dbg("JSON Prorr inválido", json_last_error_msg(), $prJson);
        } elseif (is_array($prArr) && isset($prArr['prorrogations']) && is_array($prArr['prorrogations'])) {
            foreach ($prArr['prorrogations'] as $row) {
                try {
                    $pr = new prorrogacao();
                    $pr->fk_internacao_pror   = $fk_internacao_vis;
                    $pr->fk_usuario_pror      = toIntOrNull($row['fk_usuario_pror'] ?? null);
                    $pr->acomod1_pror         = strOrNull($row['acomod1_pror'] ?? null);
                    $pr->prorrog1_ini_pror    = strOrNull($row['prorrog1_ini_pror'] ?? null);
                    $pr->prorrog1_fim_pror    = strOrNull($row['prorrog1_fim_pror'] ?? null);
                    $pr->isol_1_pror          = strOrNull($row['isol_1_pror'] ?? null);
                    $pr->diarias_1            = toIntOrNull($row['diarias_1'] ?? null);
                    $pr->fk_visita_pror       = $fk_int_visita;

                    $prorrogacaoDao->create($pr);
                } catch (Throwable $e) {
                    error_log("Erro ao criar prorrogação: " . $e->getMessage());
                    if ($__DEBUG) dbg("ERRO create PRORR", $e->getMessage(), $row);
                }
            }
        }
    }

    // ------------------- FIM CREATE ---------------------
    if ($__DEBUG) {
        dbg("CREATE concluído. Redirecionamento suprimido no debug.");
        exit;
    }
    header("Location: internacoes/lista");
    exit;
}

// ----------------------------------------------------------------------
// UPDATE (ajuste simples como no seu original)
// ----------------------------------------------------------------------
if ($type === "update") {

    try {
        $id_visita    = toIntOrNull($_POST['id_visita'] ?? null);
        $fk_hospital  = toIntOrNull($_POST['fk_hospital'] ?? null);
        $valor_diaria = strOrNull($_POST['valor_diaria'] ?? null);

        if (!$id_visita) {
            if ($__DEBUG) {
                dbg("UPDATE: id_visita inválido");
                exit;
            }
            $message->setMessage("Visita inválida.", "error", "back");
            exit;
        }

        $visitaEncontrada = $visitaDao->findById($id_visita);
        if (!$visitaEncontrada) {
            if ($__DEBUG) {
                dbg("UPDATE: visita não encontrada");
                exit;
            }
            $message->setMessage("Visita não encontrada.", "error", "back");
            exit;
        }

        // Mantém o seu padrão de update via array
        $visita = is_array($visitaEncontrada)
            ? $visitaEncontrada
            : get_object_vars($visitaEncontrada);
        $visita['id_visita']    = $id_visita;
        $visita['fk_hospital']  = $fk_hospital;
        $visita['valor_diaria'] = $valor_diaria;

        $visitaDao->update($visita);

        if ($__DEBUG) {
            dbg("UPDATE ok", $visita);
            exit;
        }

        include_once('list_visita.php');
        exit;
    } catch (Throwable $e) {
        error_log("UPDATE visita erro: " . $e->getMessage());
        if ($__DEBUG) {
            dbg("UPDATE EXCEPTION", $e->getMessage());
            exit;
        }
        $message->setMessage("Erro ao atualizar.", "error", "back");
        exit;
    }
}

// ----------------------------------------------------------------------
// DELETE (no seu fluxo vinha por GET id_visita)
// ----------------------------------------------------------------------
if ($type === "delete") {

    try {
        $id_visita = toIntOrNull($_GET['id_visita'] ?? null);

        if (!$id_visita) {
            if ($__DEBUG) {
                dbg("DELETE: id_visita inválido");
                exit;
            }
            $message->setMessage("Informações inválidas!", "error", "index.php");
            exit;
        }

        $visita = $visitaDao->findById($id_visita);
        if ($visita) {
            $visitaDao->destroy($id_visita);

            if ($__DEBUG) {
                dbg("DELETE ok", $id_visita);
                exit;
            }

            include_once('list_visita.php');
            exit;
        } else {
            if ($__DEBUG) {
                dbg("DELETE: visita não encontrada");
                exit;
            }
            $message->setMessage("Informações inválidas!", "error", "index.php");
            exit;
        }
    } catch (Throwable $e) {
        error_log("DELETE visita erro: " . $e->getMessage());
        if ($__DEBUG) {
            dbg("DELETE EXCEPTION", $e->getMessage());
            exit;
        }
        $message->setMessage("Erro ao excluir.", "error", "back");
        exit;
    }
}

// ----------------------------------------------------------------------
// Fallback: tipo desconhecido
// ----------------------------------------------------------------------
if ($__DEBUG) {
    dbg("Nenhuma ação executada. type=", $type);
    exit;
}
$message->setMessage("Ação inválida.", "error", "back");
exit;
