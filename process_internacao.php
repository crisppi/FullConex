<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("globals.php");
require_once("db.php");

require_once("models/internacao.php");
require_once("dao/internacaoDao.php");

require_once("models/gestao.php");
require_once("dao/gestaoDao.php");

require_once("models/uti.php");
require_once("dao/utiDao.php");

require_once("models/negociacao.php");
require_once("dao/negociacaoDao.php");

require_once("models/prorrogacao.php");
require_once("dao/prorrogacaoDao.php");

require_once("models/message.php");

require_once("models/usuario.php");
require_once("dao/usuarioDao.php");

require_once("models/capeante.php");
require_once("dao/capeanteDao.php");

require_once("models/detalhes.php");
require_once("dao/detalhesDao.php");

require_once("models/tuss.php");
require_once("dao/tussDao.php");

require_once("models/visita.php");
require_once("dao/visitaDao.php");

require_once("models/internacao_antecedente.php");
require_once("dao/internacaoAntecedenteDao.php");

require_once("models/alta.php");
require_once("dao/altaDao.php");


// Depurar dados enviados via POST
error_log("Dados recebidos para salvar internação:");
error_log(print_r($_POST, true));

$internAntecedenteDao = new InternacaoAntecedenteDAO($conn, $BASE_URL);

// $message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$internacaoDao = new InternacaoDAO($conn, $BASE_URL);

$gestaoDao = new gestaoDAO($conn, $BASE_URL);
$utiDao = new utiDAO($conn, $BASE_URL);
$negociacaoDao = new negociacaoDAO($conn, $BASE_URL);
$prorrogacaoDao = new prorrogacaoDAO($conn, $BASE_URL);
$capeanteDao = new capeanteDAO($conn, $BASE_URL);
$detalhesDao = new detalhesDAO($conn, $BASE_URL);
$tussDao = new tussDAO($conn, $BASE_URL);
$visitaDao = new visitaDAO($conn, $BASE_URL);
$altaDao = new altaDAO($conn, $BASE_URL);

$id_internacao = filter_input(INPUT_POST, "id_internacao");

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$typeGes = filter_input(INPUT_POST, "typeGes");

// Resgata dados do usuário
if ($type === "create") {

    // Receber os dados dos inputs
    $fk_hospital_int = filter_input(INPUT_POST, "fk_hospital_int");
    $fk_paciente_int = filter_input(INPUT_POST, "fk_paciente_int");
    $fk_patologia_int = filter_input(INPUT_POST, "fk_patologia_int") ?: 1;
    $fk_patologia2 = filter_input(INPUT_POST, "fk_patologia2") ?: 1;

    // $antecedentes = filter_input(INPUT_POST, 'antecedentes', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $jsonAntec = filter_input(INPUT_POST, 'json-antec', FILTER_DEFAULT);
    if ($jsonAntec) {
        $antecedentes = json_decode($jsonAntec, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            var_dump($antecedentes); // Exibe os dados decodificados
        } else {
            echo "Erro na decodificação do JSON: " . json_last_error_msg();
        }
    }
    if (!$jsonAntec) {
        error_log("Nenhum JSON foi recebido.");
    } else {
        error_log("JSON recebido: " . $jsonAntec);
    }

    $internado_int = filter_input(INPUT_POST, "internado_int");
    $modo_internacao_int = filter_input(INPUT_POST, "modo_internacao_int");
    $tipo_admissao_int = filter_input(INPUT_POST, "tipo_admissao_int");
    $data_visita_int = filter_input(INPUT_POST, "data_visita_int") ?: null;
    $data_intern_int = filter_input(INPUT_POST, "data_intern_int") ?: null;
    $especialidade_int = filter_input(INPUT_POST, "especialidade_int");
    $titular_int = filter_input(INPUT_POST, "titular_int");
    // Escapa caracteres especiais para evitar XSS
    $titular_int = htmlspecialchars($titular_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $titular_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $titular_int);

    $crm_int = filter_input(INPUT_POST, "crm_int");
    // Escapa caracteres especiais para evitar XSS
    $crm_int = htmlspecialchars($crm_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $crm_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $crm_int);

    $acomodacao_int = filter_input(INPUT_POST, "acomodacao_int");

    $acoes_int = filter_input(INPUT_POST, "acoes_int");
    // Escapa caracteres especiais para evitar XSS
    $acoes_int = htmlspecialchars($acoes_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $acoes_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $acoes_int);
    // Limita o tamanho do texto para 1000 caracteres
    $acoes_int = substr($acoes_int, 0, 5000);

    $rel_int = filter_input(INPUT_POST, "rel_int") ?: null;
    // Escapa caracteres especiais para evitar XSS
    $rel_int = htmlspecialchars($rel_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $rel_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $rel_int);
    // Limita o tamanho do texto para 1000 caracteres
    $rel_int = substr($rel_int, 0, 5000);

    $programacao_int = filter_input(INPUT_POST, "programacao_int");
    // Escapa caracteres especiais para evitar XSS
    $programacao_int = htmlspecialchars($programacao_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $programacao_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $programacao_int);
    // Limita o tamanho do texto para 1000 caracteres
    $programacao_int = substr($programacao_int, 0, 5000);


    $senha_int = filter_input(INPUT_POST, "senha_int");
    // Escapa caracteres especiais para evitar XSS
    $senha_int = htmlspecialchars($senha_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $senha_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $senha_int);

    $usuario_create_int = filter_input(INPUT_POST, "usuario_create_int");
    $data_create_int = filter_input(INPUT_POST, "data_create_int") ?: null;
    $grupo_patologia_int = filter_input(INPUT_POST, "grupo_patologia_int");
    $primeira_vis_int = filter_input(INPUT_POST, "primeira_vis_int");
    $visita_med_int = filter_input(INPUT_POST, "visita_med_int");
    $visita_enf_int = filter_input(INPUT_POST, "visita_enf_int");
    $visita_no_int = filter_input(INPUT_POST, "visita_no_int");
    $visita_auditor_prof_med = filter_input(INPUT_POST, "visita_auditor_prof_med");
    $visita_auditor_prof_enf = filter_input(INPUT_POST, "visita_auditor_prof_enf");
    $fk_usuario_int = filter_input(INPUT_POST, "fk_usuario_int");
    $censo_int = filter_input(INPUT_POST, "censo_int");
    $origem_int = filter_input(INPUT_POST, "origem_int");
    $int_pertinente_int = filter_input(INPUT_POST, "int_pertinente_int");
    $rel_pertinente_int = filter_input(INPUT_POST, "rel_pertinente_int");
    $hora_intern_int = filter_input(INPUT_POST, "hora_intern_int");

    //inputs da visita detalhes
    $select_detalhes = filter_input(INPUT_POST, "select_detalhes");
    $fk_vis_det = filter_input(INPUT_POST, "fk_vis_det");
    $fk_int_det = filter_input(INPUT_POST, "fk_int_det");
    $curativo_det = filter_input(INPUT_POST, "curativo_det");
    $dieta_det = filter_input(INPUT_POST, "dieta_det");
    $nivel_consc_det = filter_input(INPUT_POST, "nivel_consc_det");
    $oxig_det = filter_input(INPUT_POST, "oxig_det");
    $oxig_uso_det = filter_input(INPUT_POST, "oxig_uso_det");
    $qt_det = filter_input(INPUT_POST, "qt_det");
    $atb_det = filter_input(INPUT_POST, "atb_det");
    $dispositivo_det = filter_input(INPUT_POST, "dispositivo_det");

    $atb_uso_det = filter_input(INPUT_POST, "atb_uso_det");
    $acamado_det = filter_input(INPUT_POST, "acamado_det");
    $exames_det = filter_input(INPUT_POST, "exames_det");
    $oxigenio_hiperbarica_det = filter_input(INPUT_POST, "oxigenio_hiperbarica_det");
    $hemoderivados_det = filter_input(INPUT_POST, "hemoderivados_det");
    $dialise_det = filter_input(INPUT_POST, "dialise_det");
    // Escapa caracteres especiais para evitar XSS
    $exames_det = htmlspecialchars($exames_det, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $exames_det = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $exames_det);
    // Limita o tamanho do texto para 1000 caracteres
    $exames_det = substr($exames_det, 0, 5000);

    $oportunidades_det = filter_input(INPUT_POST, "oportunidades_det");
    // Escapa caracteres especiais para evitar XSS
    $oportunidades_det = htmlspecialchars($oportunidades_det, ENT_QUOTES, 'UTF-8');


    $oportunidades_det = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $oportunidades_det);
    // Limita o tamanho do texto para 1000 caracteres
    $oportunidades_det = substr($oportunidades_det, 0, 5000);

    $tqt_det = filter_input(INPUT_POST, "tqt_det");
    $svd_det = filter_input(INPUT_POST, "svd_det");
    $gtt_det = filter_input(INPUT_POST, "gtt_det");
    $dreno_det = filter_input(INPUT_POST, "dreno_det");
    $rt_det = filter_input(INPUT_POST, "rt_det");
    $lesoes_pele_det = filter_input(INPUT_POST, "lesoes_pele_det");
    $medic_alto_custo_det = filter_input(INPUT_POST, "medic_alto_custo_det");
    $qual_medicamento_det = filter_input(INPUT_POST, "qual_medicamento_det");

    $parto_det = filter_input(INPUT_POST, "parto_det");
    $liminar_det = filter_input(INPUT_POST, "liminar_det");
    $braden_det = filter_input(INPUT_POST, "braden_det");
    $paliativos_det = filter_input(INPUT_POST, "paliativos_det");

    // pegar dados input da gestao
    $select_gestao = filter_input(INPUT_POST, "select_gestao");
    $fk_internacao_ges = filter_input(INPUT_POST, "fk_internacao_ges");
    $fk_visita_ges = filter_input(INPUT_POST, "fk_visita_ges");

    $alto_custo_ges = filter_input(INPUT_POST, "alto_custo_ges");
    $rel_alto_custo_ges = filter_input(INPUT_POST, "rel_alto_custo_ges");
    // Escapa caracteres especiais para evitar XSS
    $rel_alto_custo_ges = htmlspecialchars($rel_alto_custo_ges, ENT_QUOTES, 'UTF-8');
    // Remove explicitamente os símbolos * e #
    $rel_alto_custo_ges = str_replace(['*', '#', 'drop', 'select', 'delete'], '', $rel_alto_custo_ges);
    // Remove caracteres especiais indesejados, incluindo * e #
    $rel_alto_custo_ges = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $rel_alto_custo_ges);
    // Remove explicitamente os símbolos * e #
    $rel_alto_custo_ges = str_replace(['*', '#'], '', $rel_alto_custo_ges);
    // Limita o tamanho do texto para 5000 caracteres
    $rel_alto_custo_ges = substr($rel_alto_custo_ges, 0, 5000);

    $opme_ges = filter_input(INPUT_POST, "opme_ges");
    $rel_opme_ges = filter_input(INPUT_POST, "rel_opme_ges");

    $home_care_ges = filter_input(INPUT_POST, "home_care_ges");
    $rel_home_care_ges = filter_input(INPUT_POST, "rel_home_care_ges");

    $desospitalizacao_ges = filter_input(INPUT_POST, "desospitalizacao_ges");
    $rel_desospitalizacao_ges = filter_input(INPUT_POST, "rel_desospitalizacao_ges");

    $fk_user_ges = filter_input(INPUT_POST, "fk_user_ges");

    $evento_adverso_ges = filter_input(INPUT_POST, "evento_adverso_ges");
    $rel_evento_adverso_ges = filter_input(INPUT_POST, "rel_evento_adverso_ges");

    $tipo_evento_adverso_gest = filter_input(INPUT_POST, "tipo_evento_adverso_gest");
    $evento_sinalizado_ges = filter_input(INPUT_POST, "evento_sinalizado_ges");
    $evento_discutido_ges = filter_input(INPUT_POST, "evento_discutido_ges");
    $evento_retorno_qual_hosp_ges = filter_input(INPUT_POST, "evento_retorno_qual_hosp_ges");
    $evento_classificado_hospital_ges = filter_input(INPUT_POST, "evento_classificado_hospital_ges");
    $evento_negociado_ges = filter_input(INPUT_POST, "evento_negociado_ges");
    $evento_valor_negoc_ges = filter_input(INPUT_POST, "evento_valor_negoc_ges");
    $evento_data_ges = filter_input(INPUT_POST, "evento_data_ges");
    $evento_encerrar_ges = filter_input(INPUT_POST, "evento_encerrar_ges");
    $evento_prorrogar_ges = filter_input(INPUT_POST, "evento_prorrogar_ges");
    $evento_impacto_financ_ges = filter_input(INPUT_POST, "evento_impacto_financ_ges");
    $evento_prolongou_internacao_ges = filter_input(INPUT_POST, "evento_prolongou_internacao_ges");
    $evento_concluido_ges = filter_input(INPUT_POST, "evento_concluido_ges");
    $evento_classificacao_ges = filter_input(INPUT_POST, "evento_classificacao_ges");
    $evento_fech_ges = filter_input(INPUT_POST, "evento_fech_ges");

    // Receber os dados dos inputs UTI
    $select_uti = filter_input(INPUT_POST, "select_uti");
    $fk_internacao_uti = filter_input(INPUT_POST, "fk_internacao_uti");
    $rel_uti = filter_input(INPUT_POST, "rel_uti") ?: null;
    $fk_paciente_int = filter_input(INPUT_POST, "fk_paciente_int");
    $internado_uti = filter_input(INPUT_POST, "internado_uti");
    $criterios_uti = filter_input(INPUT_POST, "criterios_uti");
    $data_alta_uti = filter_input(INPUT_POST, "data_alta_uti");
    $data_internacao_uti = filter_input(INPUT_POST, "data_internacao_uti");
    $dva_uti = filter_input(INPUT_POST, "dva_uti");
    $especialidade_uti = filter_input(INPUT_POST, "especialidade_uti");
    $internacao_uti = filter_input(INPUT_POST, "internacao_uti");
    $just_uti = filter_input(INPUT_POST, "just_uti");
    $motivo_uti = filter_input(INPUT_POST, "motivo_uti");
    $saps_uti = filter_input(INPUT_POST, "saps_uti");
    $score_uti = filter_input(INPUT_POST, "score_uti");
    $vm_uti = filter_input(INPUT_POST, "vm_uti");
    $id_internacao = filter_input(INPUT_POST, "id_internacao");
    $data_create_uti = filter_input(INPUT_POST, "data_create_uti") ?: null;
    $fk_user_uti = filter_input(INPUT_POST, "fk_user_uti");
    $glasgow_uti = filter_input(INPUT_POST, "glasgow_uti");
    $suporte_vent_uti = filter_input(INPUT_POST, "suporte_vent_uti");
    $dist_met_uti = filter_input(INPUT_POST, "dist_met_uti");
    $justifique_uti = filter_input(INPUT_POST, "justifique_uti");

    $hora_internacao_uti = filter_input(INPUT_POST, "hora_internacao_uti");

    // Receber os dados dos inputs prorrogacao
    $select_prorrog = filter_input(INPUT_POST, "select_prorrog");
    $fk_internacao_pror = filter_input(INPUT_POST, "fk_internacao_pror");
    $acomod1_pror = filter_input(INPUT_POST, "acomod1_pror");
    $isol_1_pror = filter_input(INPUT_POST, "isol_1_pror");
    $prorrog1_fim_pror = filter_input(INPUT_POST, "prorrog1_fim_pror") ?: null;
    $prorrog1_ini_pror = filter_input(INPUT_POST, "prorrog1_ini_pror") ?: null;
    $fk_usuario_pror = filter_input(INPUT_POST, "fk_usuario_pror");
    $fk_usuario_pror = filter_input(INPUT_POST, "fk_usuario_pror");

    // Receber os dados dos inputs neggoc
    $select_negoc = filter_input(INPUT_POST, "select_negoc");
    $fk_id_int = filter_input(INPUT_POST, "fk_id_int");
    $fk_usuario_neg = filter_input(INPUT_POST, "fk_usuario_neg");

    // Receber os dados dos inputs TUSS - bloco 1
    $select_tuss = filter_input(INPUT_POST, "select_tuss");
    $fk_int_tuss = filter_input(INPUT_POST, "fk_int_tuss");
    $tuss_liberado_sn = filter_input(INPUT_POST, "tuss_liberado_sn");
    $tuss_solicitado = filter_input(INPUT_POST, "tuss_solicitado");
    $qtd_tuss_solicitado = filter_input(INPUT_POST, "qtd_tuss_solicitado");
    $qtd_tuss_liberado = filter_input(INPUT_POST, "qtd_tuss_liberado");
    $data_realizacao_tuss = filter_input(INPUT_POST, "data_realizacao_tuss");

    // Campos para Alta (derivados ou enviados)
    $data_alta_alt = filter_input(INPUT_POST, "data_alta_alt") ?: date('Y-m-d'); // usa o campo de Data Alta do form
    $tipo_alta_alt = filter_input(INPUT_POST, "tipo_alta_alt") ?: "Alta médica"; // se não tiver campo específico no form
    $data_create_alt = date('Y-m-d'); // registro hoje
    $usuario_alt = filter_input(INPUT_POST, "usuario_create_int") ?: ($_SESSION['email_user'] ?? 'sistema');
    $fk_usuario_alt = filter_input(INPUT_POST, "fk_usuario_int") ?: ($_SESSION['id_usuario'] ?? null);
    $num_atendimento_int = filter_input(INPUT_POST, "num_atendimento_int");


    $internacao = new internacao();

    // Validação mínima de dados
    if ($type === "create") {
        $internacao->num_atendimento_int = $num_atendimento_int;
        $internacao->fk_hospital_int = $fk_hospital_int;
        $internacao->fk_paciente_int = $fk_paciente_int;
        $internacao->fk_patologia_int = $fk_patologia_int;
        $internacao->fk_patologia2 = $fk_patologia2;
        $internacao->internado_int = $internado_int;
        $internacao->modo_internacao_int = $modo_internacao_int;
        $internacao->tipo_admissao_int = $tipo_admissao_int;
        $internacao->grupo_patologia_int = $grupo_patologia_int;
        $internacao->data_visita_int = $data_visita_int;
        $internacao->data_intern_int = $data_intern_int;
        $internacao->especialidade_int = $especialidade_int;
        $internacao->titular_int = $titular_int;
        $internacao->crm_int = $crm_int;
        $internacao->acomodacao_int = $acomodacao_int;
        $internacao->rel_int = $rel_int;
        $internacao->acoes_int = $acoes_int;
        $internacao->senha_int = $senha_int;
        $internacao->usuario_create_int = $usuario_create_int;
        $internacao->data_create_int = $data_create_int;
        $internacao->grupo_patologia_int = $grupo_patologia_int;
        $internacao->primeira_vis_int = $primeira_vis_int;
        $internacao->visita_med_int = $visita_med_int;
        $internacao->visita_enf_int = $visita_enf_int;
        $internacao->visita_no_int = $visita_no_int;
        $internacao->visita_auditor_prof_med = $visita_auditor_prof_med;
        $internacao->visita_auditor_prof_enf = $visita_auditor_prof_enf;
        $internacao->fk_usuario_int = $fk_usuario_int;
        $internacao->censo_int = $censo_int;
        $internacao->programacao_int = $programacao_int;
        $internacao->origem_int = $origem_int;
        $internacao->rel_pertinente_int = $rel_pertinente_int;
        $internacao->int_pertinente_int = $int_pertinente_int;
        $internacao->hora_intern_int = $hora_intern_int;

        if ($internacaoDao->checkInternAtiva($internacao->fk_paciente_int) > 0) {
            echo "0";
        } else {
            error_log("Salvando internação com os seguintes dados: " . print_r($internacao, true));
            $lastIntern = $internacaoDao->create($internacao);

            if ($lastIntern) {
                error_log("Internação salva com sucesso. Último ID: " . $internacaoDao->findLastId()['0']['id_intern']);
            } else {
                error_log("Erro ao salvar internação.");
            }
            $lastId = $internacaoDao->findLastId()['0']['id_intern'];

            // --- Alta automática no CREATE ---
            if ($internado_int === 'n') {
                $alta = new alta();
                $alta->data_alta_alt = $data_alta_alt;
                $alta->tipo_alta_alt = $tipo_alta_alt;
                $alta->usuario_alt = $usuario_alt;
                $alta->data_create_alt = $data_create_alt;
                $alta->fk_id_int_alt = $lastId;          // importante: usar o id da internação recém-criada
                $alta->internado_alt = 'n';
                $alta->fk_usuario_alt = $fk_usuario_alt;

                // (opcional) evite duplicidade de alta no mesmo dia, se seu DAO tiver este método:
                // if (!$altaDao->existeAltaParaInternacao($lastId)) { $altaDao->create($alta); }

                $altaDao->create($alta);

                // garante o campo internado_int = 'n' (já veio como 'n', mas fica explícito)
                $internacaoData = new Internacao();
                $internacaoData->id_internacao = $lastId;
                $internacaoData->internado_int = 'n';
                // se seu DAO tiver um update específico (igual ao process de alta):
                if (method_exists($internacaoDao, 'updateAlta')) {
                    $internacaoDao->updateAlta($internacaoData);
                } else {
                    $internacaoDao->update($internacaoData);
                }
            }

            // if (isset($_FILES['intern_files'])) {
            //     // Loop through each uploaded file
            //     foreach ($_FILES['intern_files']['name'] as $index => $fileName) {
            //         $fileType = $_FILES['intern_files']['type'][$index];
            //         $fileSize = $_FILES['intern_files']['size'][$index];
            //         $tempPath = $_FILES['intern_files']['tmp_name'][$index];
            //         $fileContent = file_get_contents($tempPath);
            //         $internacaoDao->insertFiles($lastId, $fileName, $fileContent);
            //     }
            // }
            $capeante = new capeante;
            $fk_int_capeante = $lastId;
            $encerrado_cap = filter_input(INPUT_POST, "encerrado_cap");
            $aberto_cap = filter_input(INPUT_POST, "aberto_cap");
            $em_auditoria_cap = filter_input(INPUT_POST, "em_auditoria_cap");
            $senha_finalizada = filter_input(INPUT_POST, "senha_finalizada");
            $fk_user_cap = filter_input(INPUT_POST, "fk_usuario_int");
            // $usuario_create_cap = filter_input(INPUT_POST, "usuario_create_int");
            // $data_create_cap = filter_input(INPUT_POST, "data_create_int");

            $capeante->fk_int_capeante = $fk_int_capeante;
            $capeante->encerrado_cap = $encerrado_cap;
            $capeante->aberto_cap = $aberto_cap;
            $capeante->em_auditoria_cap = $em_auditoria_cap;
            $capeante->senha_finalizada = $senha_finalizada;
            $capeante->med_check = 'n';
            $capeante->enfer_check = 'n';
            $capeante->adm_check = 'n';
            // $capeante->last_cap = 1;

            $capeante->fk_user_cap = $fk_user_cap;
            $capeante->fk_int_capeante = $fk_int_capeante;
            // $capeante->usuario_create_cap = $usuario_create_cap;
            // $capeante->data_create_cap = $data_create_cap;


            $capeanteDao->create($capeante);

            // $visita = new visita;
            // $visita->fk_internacao_vis = $fk_int_capeante;
            // $visita->data_visita_vis = $data_visita_int;
            // $visita->data_create = $data_visita_int;
            // $visita->usuario_create = $usuario_create_int;
            // $visita->visita_auditor_prof_med = $visita_auditor_prof_med;
            // $visita->visita_auditor_prof_enf = $visita_auditor_prof_enf;
            // $visita->visita_med_vis = $visita_med_int;
            // $visita->visita_enf_vis = $visita_enf_int;
            // $visita->visita_no_vis = 1;
            // $visitaDao->create($visita);


            // ------------------------------
            // VISITA INICIAL (#1) AUTOMÁTICA
            // ------------------------------
            $visita = new visita();

            // sempre use o id recém criado da internação
            $visita->fk_internacao_vis = $lastId;

            // se não vier data do form, usar agora
            $dataVisita = $data_visita_int ?: date('Y-m-d H:i:s');
            $visita->data_visita_vis = $dataVisita;

            // metadados de criação
            $visita->usuario_create = $usuario_create_int ?: ($_SESSION['email_user'] ?? 'sistema');

            // flags herdadas do form, se vierem
            $visita->visita_auditor_prof_med = $visita_auditor_prof_med ?: '';
            $visita->visita_auditor_prof_enf = $visita_auditor_prof_enf ?: '';
            $visita->visita_med_vis = $visita_med_int ?: 'n';
            $visita->visita_enf_vis = $visita_enf_int ?: 'n';

            // primeira visita
            $visita->visita_no_vis = 1;

            // textos padrão da visita inicial
            $visita->rel_visita_vis = $rel_int ?: 'Visita beira Leito inicial.';
            $visita->acoes_int_vis = $acoes_int ?: 'Ações iniciais registradas.';
            $visita->oportunidades_enf = '';                         // pode preencher depois
            $visita->exames_enf = 'Sem exames relevantes no período';
            $visita->programacao_enf = $programacao_int ?: '';

            // se seu DAO aceitar, capture o ID da visita criada
            $idVisitaCriada = null;
            if (method_exists($visitaDao, 'createReturningId')) {
                $idVisitaCriada = $visitaDao->createReturningId($visita);
            } else {
                $visitaDao->create($visita);
                if (method_exists($visitaDao, 'findLastId')) {
                    $idVisitaCriada = $visitaDao->findLastId()[0]['id_visita'] ?? null;
                }
            }




            //lancar dados antecedentes
            if ($jsonAntec) {
                $antecedentes = json_decode($jsonAntec, true);

                foreach ($antecedentes as $antecedenteData) {
                    error_log("Processando antecedente: " . print_r($antecedenteData, true));
                    try {
                        $intern_antec = $internAntecedenteDao->buildintern_antec($antecedenteData);
                        $internAntecedenteDao->create($intern_antec);
                        error_log("Antecedente salvo com sucesso.");
                    } catch (Exception $e) {
                        error_log("Erro ao salvar antecedente: " . $e->getMessage());
                    }
                }
            }


            // lancar dados detalhes 
            if ($select_detalhes == "s") {

                $detalhes = new detalhes();

                // lancar dados do input detalhes se selecionado

                $detalhes->fk_int_det = $fk_int_det;
                // $detalhes->fk_vis_det = $fk_vis_det;
                $detalhes->curativo_det = $curativo_det;
                $detalhes->dieta_det = $dieta_det;
                $detalhes->nivel_consc_det = $nivel_consc_det;
                $detalhes->oxig_det = $oxig_det;
                $detalhes->oxig_uso_det = $oxig_uso_det;
                $detalhes->qt_det = $qt_det;
                $detalhes->dispositivo_det = $dispositivo_det;
                $detalhes->atb_det = $atb_det;
                $detalhes->atb_uso_det = $atb_uso_det;
                $detalhes->acamado_det = $acamado_det;
                $detalhes->exames_det = $exames_det;
                $detalhes->oportunidades_det = $oportunidades_det;
                $detalhes->tqt_det = $tqt_det;
                $detalhes->svd_det = $svd_det;
                $detalhes->gtt_det = $gtt_det;
                $detalhes->dreno_det = $dreno_det;
                $detalhes->rt_det = $rt_det;
                $detalhes->lesoes_pele_det = $lesoes_pele_det;
                $detalhes->medic_alto_custo_det = $medic_alto_custo_det;
                $detalhes->qual_medicamento_det = $qual_medicamento_det;
                $detalhes->oxigenio_hiperbarica_det = $oxigenio_hiperbarica_det;
                $detalhes->dialise_det = $dialise_det;
                $detalhes->hemoderivados_det = $hemoderivados_det;

                $detalhes->paliativos_det = $paliativos_det;
                $detalhes->braden_det = $braden_det;
                $detalhes->liminar_det = $liminar_det;
                $detalhes->parto_det = $parto_det;

                $detalhesDao->create($detalhes);
            };
            // lancar dados gestao 
            if ($select_gestao == "s") {
                $gestao = new gestao();

                // lancar dados do input gestao se selecionado

                $gestao->fk_internacao_ges = $fk_internacao_ges;
                $gestao->alto_custo_ges = $alto_custo_ges;
                $gestao->fk_visita_ges = $fk_visita_ges;

                $gestao->alto_custo_ges = $alto_custo_ges;
                $gestao->rel_alto_custo_ges = $rel_alto_custo_ges;

                $gestao->opme_ges = $opme_ges;
                $gestao->rel_opme_ges = $rel_opme_ges;

                $gestao->home_care_ges = $home_care_ges;
                $gestao->rel_home_care_ges = $rel_home_care_ges;

                $gestao->desospitalizacao_ges = $desospitalizacao_ges;
                $gestao->rel_desospitalizacao_ges = $rel_desospitalizacao_ges;

                $gestao->evento_adverso_ges = $evento_adverso_ges;
                $gestao->rel_evento_adverso_ges = $rel_evento_adverso_ges;
                $gestao->tipo_evento_adverso_gest = $tipo_evento_adverso_gest;
                $gestao->evento_retorno_qual_hosp_ges = $evento_retorno_qual_hosp_ges;
                $gestao->evento_classificado_hospital_ges = $evento_classificado_hospital_ges;
                $gestao->evento_data_ges = $evento_data_ges;
                $gestao->evento_encerrar_ges = $evento_encerrar_ges;
                $gestao->evento_impacto_financ_ges = $evento_impacto_financ_ges;
                $gestao->evento_prolongou_internacao_ges = $evento_prolongou_internacao_ges;
                $gestao->evento_concluido_ges = $evento_concluido_ges;
                $gestao->evento_classificacao_ges = $evento_classificacao_ges;
                $gestao->evento_fech_ges = $evento_fech_ges;
                $gestao->fk_user_ges = $fk_user_ges;

                $gestaoDao->create($gestao);
            };
            // lancar dados UTI 
            if ($select_uti == "s") {

                $uti = new uti();

                // lancar dados do input uti se selecionado
                $uti->fk_internacao_uti = $fk_internacao_uti;
                $uti->internado_uti = $internado_uti;
                $uti->criterios_uti = $criterios_uti;
                $uti->data_alta_uti = $data_alta_uti;
                $uti->data_internacao_uti = $data_internacao_uti;
                $uti->dva_uti = $dva_uti;
                $uti->especialidade_uti = $especialidade_uti;
                $uti->internacao_uti = $internacao_uti;
                $uti->just_uti = $just_uti;
                $uti->motivo_uti = $motivo_uti;
                $uti->rel_uti = $rel_uti;
                $uti->saps_uti = $saps_uti;
                $uti->score_uti = $score_uti;
                $uti->vm_uti = $vm_uti;
                $uti->id_internacao = $id_internacao;
                $uti->usuario_create_uti = $usuario_create_int;
                $uti->data_create_uti = $data_create_int;
                $uti->fk_user_uti = $fk_user_uti;
                $uti->glasgow_uti = $glasgow_uti;
                $uti->suporte_vent_uti = $suporte_vent_uti;
                $uti->justifique_uti = $justifique_uti;
                $uti->hora_internacao_uti = $hora_internacao_uti;
                $uti->dist_met_uti = $dist_met_uti;

                $utiDao->create($uti);
            };


            echo ("Valor de \$select_negoc recebido: " . ($select_negoc ?? "NULO"));
            var_dump($select_negoc);
            echo "Valor de \$select_negoc recebido: " . ($select_negoc ?? "NULO") . "<br>";

            // lancar dados negociacao 
            if ($select_negoc === "s") {
                error_log("[DEBUG] Iniciando processamento de negociações...");

                // Verifica se o JSON foi recebido corretamente
                if (!isset($_POST['negociacoes_json']) || empty($_POST['negociacoes_json'])) {
                    error_log("[ERRO] O campo 'negociacoes_json' não foi enviado ou está vazio.");
                } else {
                    error_log("[DEBUG] JSON recebido: " . $_POST['negociacoes_json']);
                }

                // Obtém o JSON ou define um array vazio
                $negociacoesJSON = $_POST['negociacoes_json'] ?? '[]';
                $negociacoesArray = json_decode($negociacoesJSON, true);

                // Verifica se a decodificação do JSON foi bem-sucedida
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("[ERRO] Falha ao decodificar JSON: " . json_last_error_msg());
                } else {
                    error_log("[DEBUG] JSON decodificado corretamente.");
                }

                // Se não houver negociações válidas, continua o fluxo
                if (!is_array($negociacoesArray) || count($negociacoesArray) === 0) {
                    error_log("[ALERTA] Nenhuma negociação foi enviada ou válida. Prosseguindo com o restante do processo.");
                } else {
                    error_log("[DEBUG] Total de negociações recebidas: " . count($negociacoesArray));

                    foreach ($negociacoesArray as $index => $negociacaoData) {
                        error_log("[DEBUG] Processando negociação #$index: " . print_r($negociacaoData, true));

                        // Validação dos campos
                        $trocaDe = ($negociacaoData['troca_de']);
                        $trocaPara = ($negociacaoData['troca_para']);
                        $qtd = filter_var($negociacaoData['qtd'], FILTER_VALIDATE_INT);
                        $saving = filter_var($negociacaoData['saving'], FILTER_VALIDATE_FLOAT);

                        $tipo_negociacao = filter_var($negociacaoData['tipo_negociacao']);
                        $data_inicio_negoc = ($negociacaoData['data_inicio_negoc']);
                        $data_fim_negoc = ($negociacaoData['data_fim_negoc']);

                        if (!$trocaDe || !$trocaPara || !$qtd || $saving === false) {
                            error_log("[ERRO] Negociação inválida ignorada: " . print_r($negociacaoData, true));
                            continue;
                        }

                        error_log("[DEBUG] Valores validados corretamente.");

                        // Criando a negociação
                        $negociacao = new Negociacao();
                        $negociacao->fk_id_int = $negociacaoData['fk_id_int'];
                        $negociacao->fk_usuario_neg = $negociacaoData['fk_usuario_neg'];
                        $negociacao->troca_de = $trocaDe;
                        $negociacao->troca_para = $trocaPara;
                        $negociacao->qtd = $qtd;
                        $negociacao->saving = $saving;

                        $negociacao->tipo_negociacao = $tipo_negociacao;
                        $negociacao->data_inicio_neg = $data_inicio_negoc;
                        $negociacao->data_fim_neg = $data_fim_negoc;

                        error_log("[DEBUG] Objeto de negociação criado: " . print_r($negociacao, true));

                        // Verifica duplicidade antes de criar
                        if (!$negociacaoDao->existeNegociacao($negociacao)) {
                            if (!$negociacaoDao->create($negociacao)) {
                                error_log("[ERRO] Falha ao salvar negociação: " . print_r($negociacao, true));
                            } else {
                                error_log("[SUCESSO] Negociação salva com sucesso." . print_r($negociacao, true));
                            }
                        } else {
                            error_log("[ALERTA] Negociação duplicada ignorada: " . print_r($negociacao, true));
                        }
                    }
                }
            }


            // lancar dados prorrogacao 
            if ($select_prorrog == "s") {

                // Obtém o JSON das prorrogações enviado pelo formulário
                $prorrogacoesJson = $_POST['prorrogacoes-json'] ?? '[]';

                // Decodifica o JSON
                $prorrogacoesArray = json_decode($prorrogacoesJson, true);

                // Verifica se houve erro na decodificação
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $jsonError = json_last_error_msg();
                    throw new Exception("Erro ao decodificar o JSON de prorrogações: " . $jsonError);
                }

                // Valida se o formato do JSON está correto
                if (is_array($prorrogacoesArray) && isset($prorrogacoesArray['prorrogations'])) {

                    // Itera pelas entradas de prorrogações
                    foreach ($prorrogacoesArray['prorrogations'] as $prorrogacaoData) {

                        // Preenche o objeto prorrogação com os dados do JSON
                        $prorrogacao = new prorrogacao();
                        $prorrogacao->fk_internacao_pror = $prorrogacaoData['fk_internacao_pror'];
                        $prorrogacao->fk_usuario_pror = $prorrogacaoData['fk_usuario_pror'];
                        $prorrogacao->acomod1_pror = $prorrogacaoData['acomod1_pror'];
                        $prorrogacao->prorrog1_ini_pror = $prorrogacaoData['prorrog1_ini_pror'];
                        $prorrogacao->prorrog1_fim_pror = $prorrogacaoData['prorrog1_fim_pror'];
                        $prorrogacao->isol_1_pror = $prorrogacaoData['isol_1_pror'] ?? null;
                        $prorrogacao->diarias_1 = $prorrogacaoData['diarias_1'];

                        // Insere no banco
                        $prorrogacaoDao->create($prorrogacao);
                    }
                } else {
                    throw new Exception("Formato de JSON inválido para prorrogações.");
                }
            }

            // Lançar dados TUSS
            if ($select_tuss == "s") {
                // Decodifica o JSON enviado pelo input tuss-json
                $tussJson = isset($_POST['tuss-json']) ? $_POST['tuss-json'] : '[]';
                $tussArray = json_decode($tussJson, true);

                // Verifica se o JSON foi decodificado corretamente
                if (is_array($tussArray) && isset($tussArray['tussEntries'])) {
                    foreach ($tussArray['tussEntries'] as $tussData) {
                        $tuss = new tuss();

                        // Preenche os campos do objeto tuss com os dados do JSON
                        $tuss->fk_int_tuss = $tussData['fk_int_tuss'] ?? null;
                        $tuss->fk_usuario_tuss = $tussData['fk_usuario_tuss'] ?? null;
                        $tuss->tuss_solicitado = $tussData['tuss_solicitado'] ?? null;
                        $tuss->data_realizacao_tuss = $tussData['data_realizacao_tuss'] ?? null;
                        $tuss->qtd_tuss_solicitado = $tussData['qtd_tuss_solicitado'] ?? null;
                        $tuss->qtd_tuss_liberado = $tussData['qtd_tuss_liberado'] ?? null;
                        $tuss->tuss_liberado_sn = $tussData['tuss_liberado_sn'] ?? null;

                        // Chama o método DAO para salvar os dados no banco
                        $tussDao->create($tuss);
                    }
                } else {
                    // Erro ao decodificar o JSON
                    throw new Exception("Erro ao processar os dados de TUSS.");
                }
            }

            // header("location:list_internacao.php");

            echo "lancado internacao";
        }
    };
}

if ($type == "update") {
    // Receber os dados dos inputs
    $fk_hospital_int = filter_input(INPUT_POST, "fk_hospital_int");
    $fk_paciente_int = filter_input(INPUT_POST, "fk_paciente_int");
    $fk_patologia_int = filter_input(INPUT_POST, "fk_patologia_int") ?: 1;
    $fk_patologia2 = filter_input(INPUT_POST, "fk_patologia2") ?: 1;
    $internado_int = filter_input(INPUT_POST, "internado_int");
    $modo_internacao_int = filter_input(INPUT_POST, "modo_internacao_int");
    $tipo_admissao_int = filter_input(INPUT_POST, "tipo_admissao_int");
    $data_visita_int = filter_input(INPUT_POST, "data_visita_int") ?: null;
    $data_intern_int = filter_input(INPUT_POST, "data_intern_int") ?: null;
    $especialidade_int = filter_input(INPUT_POST, "especialidade_int");
    $titular_int = filter_input(INPUT_POST, "titular_int");
    $crm_int = filter_input(INPUT_POST, "crm_int");
    $acomodacao_int = filter_input(INPUT_POST, "acomodacao_int");
    $acoes_int = filter_input(INPUT_POST, "acoes_int");
    // Escapa caracteres especiais para evitar XSS
    $acoes_int = htmlspecialchars($acoes_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $acoes_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $acoes_int);
    // Limita o tamanho do texto para 1000 caracteres
    $acoes_int = substr($acoes_int, 0, 5000);

    $rel_int = filter_input(INPUT_POST, "rel_int");
    // Escapa caracteres especiais para evitar XSS
    $rel_int = htmlspecialchars($rel_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $rel_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $rel_int);
    // Limita o tamanho do texto para 1000 caracteres
    $rel_int = substr($rel_int, 0, 5000);

    $programacao_int = filter_input(INPUT_POST, "programacao_int");
    // Escapa caracteres especiais para evitar XSS
    $programacao_int = htmlspecialchars($programacao_int, ENT_QUOTES, 'UTF-8');
    // Remove caracteres especiais indesejados
    $programacao_int = preg_replace("/[^a-zA-Z0-9À-ÖØ-öø-ÿ .,!?()\-]/u", "", $programacao_int);
    // Limita o tamanho do texto para 1000 caracteres
    $programacao_int = substr($programacao_int, 0, 5000);

    $senha_int = filter_input(INPUT_POST, "senha_int");
    $usuario_create_int = filter_input(INPUT_POST, "usuario_create_int");
    $data_create_int = filter_input(INPUT_POST, "data_create_int") ?: null;
    $grupo_patologia_int = filter_input(INPUT_POST, "grupo_patologia_int");
    $primeira_vis_int = filter_input(INPUT_POST, "primeira_vis_int");
    $visita_med_int = filter_input(INPUT_POST, "visita_med_int");
    $visita_enf_int = filter_input(INPUT_POST, "visita_enf_int");
    $visita_no_int = filter_input(INPUT_POST, "visita_no_int");
    $visita_auditor_prof_med = filter_input(INPUT_POST, "visita_auditor_prof_med");
    $visita_auditor_prof_enf = filter_input(INPUT_POST, "visita_auditor_prof_enf");
    $fk_usuario_int = filter_input(INPUT_POST, "fk_usuario_int");
    $censo_int = filter_input(INPUT_POST, "censo_int");
    $origem_int = filter_input(INPUT_POST, "origem_int");
    $int_pertinente_int = filter_input(INPUT_POST, "int_pertinente_int");
    $rel_pertinente_int = filter_input(INPUT_POST, "rel_pertinente_int");
    $hora_intern_int = filter_input(INPUT_POST, "hora_intern_int");

    $id_internacao = filter_input(INPUT_POST, "id_internacao");

    $internacao = new internacao();

    if (3 < 4) {

        $internacao->fk_hospital_int = $fk_hospital_int;
        $internacao->fk_paciente_int = $fk_paciente_int;
        $internacao->fk_patologia_int = $fk_patologia_int;
        $internacao->fk_patologia2 = $fk_patologia2;
        $internacao->internado_int = $internado_int;
        $internacao->modo_internacao_int = $modo_internacao_int;
        $internacao->tipo_admissao_int = $tipo_admissao_int;
        $internacao->grupo_patologia_int = $grupo_patologia_int;
        $internacao->data_visita_int = $data_visita_int;
        $internacao->data_intern_int = $data_intern_int;
        $internacao->especialidade_int = $especialidade_int;
        $internacao->titular_int = $titular_int;
        $internacao->crm_int = $crm_int;
        $internacao->rel_int = $rel_int;
        $internacao->acoes_int = $acoes_int;
        $internacao->programacao_int = $programacao_int;
        $internacao->senha_int = $senha_int;
        $internacao->usuario_create_int = $usuario_create_int;
        $internacao->data_create_int = $data_create_int;
        $internacao->grupo_patologia_int = $grupo_patologia_int;
        $internacao->primeira_vis_int = $primeira_vis_int;
        $internacao->visita_med_int = $visita_med_int;
        $internacao->visita_enf_int = $visita_enf_int;
        $internacao->visita_no_int = $visita_no_int;
        $internacao->acomodacao_int = $acomodacao_int;
        $internacao->visita_auditor_prof_med = $visita_auditor_prof_med;
        $internacao->visita_auditor_prof_enf = $visita_auditor_prof_enf;
        $internacao->fk_usuario_int = $fk_usuario_int;
        $internacao->censo_int = $censo_int;
        $internacao->origem_int = $origem_int;
        $internacao->rel_pertinente_int = $rel_pertinente_int;
        $internacao->int_pertinente_int = $int_pertinente_int;
        $internacao->hora_intern_int = $hora_intern_int;

        $internacao->id_internacao = $id_internacao;
        $internacaoDao->update($internacao);

        // lancar dados UTI 
        if ($select_uti == "s") {

            $uti = new uti();

            // lancar dados do input uti se selecionado
            $uti->fk_internacao_uti = $fk_internacao_uti;
            $uti->internado_uti = $internado_uti;
            $uti->criterios_uti = $criterios_uti;
            $uti->data_alta_uti = $data_alta_uti;
            $uti->data_internacao_uti = $data_internacao_uti;
            $uti->dva_uti = $dva_uti;
            $uti->especialidade_uti = $especialidade_uti;
            $uti->internacao_uti = $internacao_uti;
            $uti->just_uti = $just_uti;
            $uti->motivo_uti = $motivo_uti;
            $uti->rel_uti = $rel_uti;
            $uti->saps_uti = $saps_uti;
            $uti->score_uti = $score_uti;
            $uti->vm_uti = $vm_uti;
            $uti->id_internacao = $id_internacao;
            $uti->usuario_create_uti = $usuario_create_int;
            $uti->data_create_uti = $data_create_uti;
            $uti->glasgow_uti = $glasgow_uti;
            $uti->suporte_vent_uti = $suporte_vent_uti;
            $uti->justifique_uti = $justifique_uti;
            $uti->hora_internacao_uti = $hora_internacao_uti;
            $uti->dist_met_uti = $dist_met_uti;

            $utiDao->create($uti);
        };
        var_dump($select_negoc);
        echo "Valor de \$select_negoc recebido: " . ($select_negoc ?? "NULO") . "<br>";
        // lancar dados negociacao 
        if ($select_negoc === "s") {
            error_log("Recebendo negociações...");

            $negociacoesJSON = $_POST['negociacoes_json'] ?? '[]'; // Obtém o JSON ou define um array vazio
            error_log("JSON recebido: " . $negociacoesJSON);

            $negociacoesArray = json_decode($negociacoesJSON, true);
            if (!is_array($negociacoesArray) || count($negociacoesArray) === 0) {
                error_log("Nenhuma negociação válida recebida. Prosseguindo...");
            } else {
                foreach ($negociacoesArray as $negociacaoData) {
                    error_log("Processando negociação: " . print_r($negociacaoData, true));

                    // Validação dos campos
                    $trocaDe = filter_var($negociacaoData['troca_de'], FILTER_VALIDATE_INT);
                    $trocaPara = filter_var($negociacaoData['troca_para'], FILTER_VALIDATE_INT);
                    $qtd = filter_var($negociacaoData['qtd'], FILTER_VALIDATE_INT);
                    $saving = filter_var($negociacaoData['saving'], FILTER_VALIDATE_FLOAT);

                    if (!$trocaDe || !$trocaPara || !$qtd || $saving === false) {
                        error_log("Negociação inválida ignorada: " . print_r($negociacaoData, true));
                        continue;
                    }

                    error_log("Negociação validada com sucesso. Criando objeto...");

                    // Criar objeto negociação
                    $negociacao = new Negociacao();
                    $negociacao->fk_id_int = $negociacaoData['fk_id_int'];
                    $negociacao->fk_usuario_neg = $negociacaoData['fk_usuario_neg'];
                    $negociacao->troca_de = $trocaDe;
                    $negociacao->troca_para = $trocaPara;
                    $negociacao->qtd = $qtd;
                    $negociacao->saving = $saving;

                    error_log("Verificando duplicidade...");
                    if (!$negociacaoDao->existeNegociacao($negociacao)) {
                        error_log("Nenhuma duplicidade encontrada. Salvando...");
                        if ($negociacaoDao->create($negociacao)) {
                            error_log("Negociação salva com sucesso!");
                        } else {
                            error_log("Erro ao salvar negociação no banco.");
                        }
                    } else {
                        error_log("Negociação duplicada encontrada. Ignorando...");
                    }
                }
            }
        }
    }

    if ($select_prorrog == "s") {

        $prorrogacao = new prorrogacao();

        // lancar dados do input prorrogacao se selecionado
        $prorrogacao->fk_internacao_pror = $fk_internacao_pror;
        $prorrogacao->acomod1_pror = $acomod1_pror;
        $prorrogacao->isol_1_pror = $isol_1_pror;
        $prorrogacao->prorrog1_fim_pror = $prorrog1_fim_pror;
        $prorrogacao->prorrog1_ini_pror = $prorrog1_ini_pror;
        $prorrogacao->fk_usuario_pror = $fk_usuario_pror;

        $prorrogacaoDao->create($prorrogacao);
    };

    if ($select_tuss == "s") {

        $tuss = new tuss();

        // lancar dados do input tuss se selecionado
        $tuss->fk_int_tuss = $fk_int_tuss;
        $tuss->tuss_solicitado = $tuss_solicitado;
        $tuss->data_realizacao_tuss = $data_realizacao_tuss;
        $tuss->qtd_tuss_solicitado = $qtd_tuss_solicitado;
        $tuss->qtd_tuss_liberado = $qtd_tuss_liberado;
        $tuss->tuss_liberado_sn = $tuss_liberado_sn;
        $tussDao->create($tuss);
    };
    // };

    header("location:list_internacao.php");
    // echo "1";
}
// echo "0";