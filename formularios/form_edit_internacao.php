    <?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);   // para alguns módulos
    error_reporting(E_ALL);

    include_once("check_logado.php");

    require_once("templates/header.php");

    include_once("models/internacao.php");
    include_once("dao/internacaoDao.php");

    include_once("models/message.php");

    include_once("models/hospital.php");
    include_once("dao/hospitalDao.php");

    include_once("models/patologia.php");
    include_once("dao/patologiaDao.php");

    include_once("models/paciente.php");
    include_once("dao/pacienteDao.php");

    include_once("models/uti.php");
    include_once("dao/utiDao.php");

    include_once("models/gestao.php");
    include_once("dao/gestaoDao.php");

    include_once("models/prorrogacao.php");
    include_once("dao/prorrogacaoDao.php");

    include_once("models/negociacao.php");
    include_once("dao/negociacaoDao.php");

    include_once("models/capeante.php");
    include_once("dao/capeanteDao.php");

    include_once("models/hospitalUser.php");
    include_once("dao/hospitalUserDao.php");

    include_once("models/tuss_ans.php");
    include_once("dao/tussAnsDao.php");

    include_once("models/tuss.php");
    include_once("dao/tussDao.php");

    include_once("models/detalhes.php");
    include_once("dao/detalhesDao.php");

    include_once("array_dados.php");

    include_once("dao/cidDao.php");
    $cid = new cidDAO($conn, $BASE_URL);
    $cids = $cid->findAll();
    $internacaoDao = new internacaoDAO($conn, $BASE_URL);

    $hospital_geral = new hospitalDAO($conn, $BASE_URL);
    $hospitals = $hospital_geral->findGeral($limite, $inicio);

    $hospitalList = new hospitalUserDAO($conn, $BASE_URL);
    $hospitalUser = new hospitalUserDAO($conn, $BASE_URL);

    $pacienteDao = new pacienteDAO($conn, $BASE_URL);
    $pacientes = $pacienteDao->findGeral($limite, $inicio);

    $patologiaDao = new patologiaDAO($conn, $BASE_URL);
    $patologias = $patologiaDao->findGeral();

    // ---------- GESTÃO ------------------------------------------
    $gestaoDao  = new gestaoDAO($conn, $BASE_URL);
    $int_gestao = $gestaoDao->findByIdInt($intern['id_internacao']);
    if (empty($int_gestao)) {
        // Não existem gestões para esta internação
    } else {
        // $int_gestao é um array ou objeto Gestao, dependendo da opção escolhida
    }

    /* ---------- UTI ------------------------------------------ */
    $utiDao  = new utiDAO($conn, $BASE_URL);

    /* carrega TODAS as passagens em UTI desta internação */
    $utiList = $utiDao->selectInternacaoUti($intern['id_internacao']);  // método que criamos
    $u = null;
    if (!empty($utiList)) {
        $u = $utiList[0];
    }

    /* se não houver registro ainda, cria 1 linha vazia como placeholder */
    if (!$utiList) {
        $utiList[] = [
            'entrada'           => '',
            'hora'              => '',
            'saida'             => '',
            'motivo_uti'        => '',
            'rel_uti'           => '',
            'vent'              => 'n',
            'saps_uti'          => '',
            'score_uti'         => '',
            'glasgow_uti'       => '',
            'dist_met_uti'      => '',
            'suporte_vent_uti'  => '',
            'justifique_uti'    => '',
            'criterios_uti'     => '',
            'dva_uti'           => '',
            'especialidade_uti' => '',
            'internacao_uti'    => '',
            'internado_uti'     => '',
            'just_uti'          => '',
            'fk_visita_uti'     => ''
        ];
    }


    // ---------- TUSS ------------------------------------------
    $tuss = new tussDAO($conn, $BASE_URL);
    $tussDaInt = $tuss->selectTUSSByIntern($intern['id_internacao']);
    if (empty($tussDaInt)) {
        // Não existem detalhes para esta internação
    } else {
        foreach ($tussDaInt as $tussInt) {
            // $det é um array ou objeto Detalhes, dependendo da opção escolhida
        }
    }

    $capeante = new capeanteDAO($conn, $BASE_URL);
    $CapIdMax = $capeante->findMaxCapeante();

    // ---------- PRORROGAÇÃO ------------------------------------------
    $prorDao   = new prorrogacaoDAO($conn, $BASE_URL);
    $prorList  = $prorDao->selectInternacaoProrrog($intern['id_internacao']);
    if (empty($prorList)) {
        // Não existem prorrogações para esta internação
    } else {
        foreach ($prorList as $pror) {
            // $pror é um array ou objeto Prorrogação, dependendo da opção escolhida
        }
    }
    /* se não vier nada, cria um placeholder */
    if (!$prorList) {
        $prorList[] = [
            'acomod'      => '',
            'ini'         => '',
            'fim'         => '',
            'diarias'     => '',
            'isolamento'  => 'n',
        ];
    }

    // ---------- negociacao ------------------------------------------
    $negociacao = new negociacaoDAO($conn, $BASE_URL);
    $negociacoesInt = $negociacao->findByInternacao($intern['id_internacao']); // implemente este método no DAO

    // ---- normaliza o array ----------------------------------------------------
    $negociacoesInt = array_map(static fn($n) => (array)$n, $negociacoesInt ?? []);
    if (!$negociacoesInt) {
        // placeholder vazio para pelo menos uma linha
        $negociacoesInt[] = [
            'tipo_negociacao'    => '',
            'data_inicio_negoc' => '',
            'data_fim_negoc'    => '',
            'troca_de'          => '',
            'troca_para'        => '',
            'qtd'               => '',
            'saving'            => ''
        ];
    }
    /*  depois de ler do banco  ------------------------------ */
    if (!isset($negociacoesInt) || !is_array($negociacoesInt)) {
        $negociacoesInt = [];          // evita “Undefined variable”
    }
    /*  garante 0-ou-mais linhas  */
    $negociacoesInt = array_map(fn($n) => (array)$n, $negociacoesInt);
    if (!$negociacoesInt) {
        $negociacoesInt[] = [ /* campos vazios */];
    }

    // ---------- DETALHES ------------------------------------------
    $detalhesDao = new detalhesDao($conn, $BASE_URL);
    $detalhesDaInt = $detalhesDao->findByInternacao($intern['id_internacao']);
    if (empty($detalhesDaInt)) {
        // Não existem detalhes para esta internação
    } else {
        foreach ($detalhesDaInt as $det) {
            // $det é um array ou objeto Detalhes, dependendo da opção escolhida
        }
    }
    if (empty($int_detalhes)) {
        $detalhes_new = new Detalhes();
        $int_detalhes = $detalhes_new;
    }

    $haDetalhes = !empty($detalhesDaInt);   // true  se encontrou registros

    $where = $order = $obLimite = null;
    $query = $hospitalUser->selectAllhospitalUser($where, $order, $obLimite);

    // SELECIONAR HOSPITAL POR USUARIO
    $id_hospitalUser = ($_SESSION['id_usuario']);

    $listHopitaisPerfil = $hospitalList->joinHospitalUser($id_hospitalUser);

    $tuss = new tussAnsDAO($conn, $BASE_URL);

    $tuss_int = new tussDAO($conn, $BASE_URL);

$id_internacao = filter_input(INPUT_GET, 'id_internacao') ? filter_input(INPUT_GET, 'id_internacao') : 1;

$intern = $internacaoDao->findByIdArray($id_internacao)[0];
$dataLancamentoAtual = '';
if (!empty($intern['data_lancamento_int']) && $intern['data_lancamento_int'] !== '0000-00-00 00:00:00') {
    $tsLanc = strtotime($intern['data_lancamento_int']);
    if ($tsLanc) {
        $dataLancamentoAtual = date('Y-m-d\TH:i', $tsLanc);
    }
}
    $int_paciente = $pacienteDao->findById($intern['fk_paciente_int']);
    $int_patologia = $patologiaDao->findById($intern['fk_patologia_int']);
    $int_antecedente = $patologiaDao->findById($intern['fk_patologia2']);
    $int_detalhes = $detalhesDao->findById($intern['id_internacao']);
    $ctl_detalhes = $detalhesDao->findById($intern['id_internacao']);
    $int_hospital = $hospital_geral->findById($intern['fk_hospital_int']);
    $tussInt = $tuss_int->findByIdIntern($intern['id_internacao'] ?? 0);
    $int_gestao = $gestao->findByIdInt($intern['id_internacao']);

    $tussGeral = $tuss->findAll();

    ?>


    <div class="row card" style="background-color: #f6f6f7ff; margin-top:-35px">
        <div class="form-group row">
            <h4 class="text-center w-100" style="
                margin: 10px;
                background-color: #5e2363;
                color: #fff;
                padding: 10px 0;
                border-radius: 0.25rem;
            ">Editar internação</h4>
            <hr class="w-100 margin-top: 20px mb-4">

            <!-- <hr class="w-100 margin-top: 20px mb-4"> -->
            <!-- </div> -->
            <form class="visible" action="process_internacao_editar.php" id="myForm" method="POST"
                enctype="multipart/form-data">
                <div style="text-align: right;">
                    <p style="font-size: .6em; color:red; margin-top: -20px;">* Campos Obrigatórios</p>
                </div>
                <!-- ID da internação (necessário no update) -->
                <input type="hidden" id="id_internacao" name="id_internacao" value="<?= $intern['id_internacao'] ?>">

                <input type="hidden" name="type" value="update_editar">

                <p style="display:none" id="proximoId_int">0</p>
                <input type="hidden" value="n" id="censo_int" name="censo_int">
                <input type="hidden" value="<?= $_SESSION["id_usuario"] ?>" id="fk_usuario_int" name="fk_usuario_int">
                <div class="form-group row align-items-end">

                    <!-- Hospital (Somente leitura) -->
                    <div class="form-group col-sm-3 mb-2">
                        <label class="control-label">Hospital</label>
                        <input type="text" class="form-control form-control-sm" readonly value="<?php
                                                                                                foreach ($hospitals as $hospital) {
                                                                                                    if ($hospital['id_hospital'] == $intern['fk_hospital_int']) {
                                                                                                        echo $hospital['nome_hosp'];
                                                                                                        break;
                                                                                                    }
                                                                                                }
                                                                                                ?>">
                        <input type="hidden" name="fk_hospital_int" value="<?= $intern['fk_hospital_int'] ?>">
                    </div>

                    <!-- Paciente (Somente leitura) -->
                    <div class="form-group col-sm-3 mb-2">
                        <label class="control-label">Paciente</label>
                        <input type="text" class="form-control form-control-sm" readonly value="<?php
                                                                                                foreach ($pacientes as $paciente) {
                                                                                                    if ($paciente['id_paciente'] == $intern['fk_paciente_int']) {
                                                                                                        echo $paciente['nome_pac'];
                                                                                                        break;
                                                                                                    }
                                                                                                }
                                                                                                ?>">
                        <input type="hidden" name="fk_paciente_int" value="<?= $intern['fk_paciente_int'] ?>">
                    </div>

                    <!-- Data Internação -->
                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="data_intern_int">
                            <span style="color: red;">*</span> Data Internação
                        </label>
                        <input type="date" class="form-control form-control-sm" id="data_intern_int"
                            name="data_intern_int" value="<?= $intern["data_intern_int"] ?>" required>
                    </div>

                    <!-- Hora -->
                    <div class="form-group col-sm-1 mb-2">
                        <label class="control-label" for="hora_intern_int">Hora</label>
                        <input type="time" class="form-control form-control-sm" id="hora_intern_int"
                            name="hora_intern_int" value="<?= date('H:i', strtotime($intern['hora_intern_int'])); ?>">
                    </div>

                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="data_lancamento_int">Data lançamento</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="data_lancamento_int"
<<<<<<< Updated upstream
                            name="data_lancamento_int" value="<?= $dataLancamentoAtual ?>" readonly>
=======
                            name="data_lancamento_int" value="<?= $dataLancamentoAtual ?>" readonly tabindex="-1"
                            onfocus="this.blur();" onkeydown="return false;" style="cursor:not-allowed;">
>>>>>>> Stashed changes
                    </div>
                </div>

                <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
                <input type="hidden" value="s" id="primeira_vis_int" name="primeira_vis_int">
                <input type="hidden" value="0" id="visita_no_int" name="visita_no_int">
                <input type="hidden" id="visita_enf_int" name="visita_enf_int" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                            echo 's';
                                                                                        } else {
                                                                                            echo 'n';
                                                                                        }; ?>">

                <input type="hidden" id="visita_med_int" name="visita_med_int" value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                            echo 's';
                                                                                        } else {
                                                                                            echo 'n';
                                                                                        }; ?>">

                <input type="hidden" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf" value="<?php if (($_SESSION['cargo']) === 'Enf_Auditor') {
                                                                                                            echo ($_SESSION['email_user']);
                                                                                                        }; ?>">
                <input type="hidden" id="visita_auditor_prof_med" name="visita_auditor_prof_med" value="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                            echo ($_SESSION['email_user']);
                                                                                                        }; ?>">


                <?php
                $cidSelecionado = isset($intern['fk_cid_int']) ? (int)$intern['fk_cid_int'] : null;
                ?>
                <div class="row align-items-end">
                    <!-- Data Visita -->
                    <div class="form-group col-sm-2 mb-2">
                        <label for="data_visita_int"><span style="color: red;">*</span> Data Visita</label>
                        <input type="date" class="form-control form-control-sm" id="data_visita_int"
                            name="data_visita_int" value="<?= date('Y-m-d'); ?>">
                    </div>

                    <!-- Internado -->
                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="internado_int">Internado</label>
                        <select class="form-control-sm form-control" id="internado_int" name="internado_int">
                            <option value="s" <?= $intern['internado_int'] == 's' ? 'selected' : '' ?>>Sim</option>
                            <option value="n" <?= $intern['internado_int'] == 'n' ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="acomodacao_int">Acomodação</label>
                        <select class="form-control-sm form-control" id="acomodacao_int" name="acomodacao_int">
                            <option value="">Selecione</option>
                            <?php
                            sort($dados_acomodacao, SORT_ASC);
                            foreach ($dados_acomodacao as $acomd) {
                                // Verifica se o valor da acomodação corresponde ao valor vindo do banco
                                $selected = ($acomd == $intern['acomodacao_int']) ? 'selected' : '';
                            ?>
                            <option value="<?= $acomd; ?>" <?= $selected; ?>><?= $acomd; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="especialidade_int">Especialidade</label>
                        <input list="especialidade-options" class="form-control-sm form-control" id="especialidade_int"
                            name="especialidade_int" value="<?= htmlspecialchars($intern['especialidade_int'] ?? '') ?>"
                            placeholder="Selecione ou digite">
                        <datalist id="especialidade-options">
                            <?php
                            sort($dados_especialidade, SORT_ASC);
                            foreach ($dados_especialidade as $especial) {
                                echo '<option value="' . htmlspecialchars($especial) . '"></option>';
                            }
                            ?>
                        </datalist>
                    </div>

                    <div class="form-group col-sm-3 mb-2">
                        <label for="titular_int">Médico</label>
                        <input type="text" maxlength="100" class="form-control form-control-sm" id="titular_int"
                            value="<?= $intern["titular_int"] ?>" name="titular_int">
                    </div>
                    <div class="form-group col-sm-1 mb-2">
                        <label for="crm_int">CRM</label>
                        <input type="text" maxlength="10" class="form-control form-control-sm" id="crm_int"
                            name="crm_int" value="<?= $intern["crm_int"] ?>">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="modo_internacao_int">Modo Admissão</label>
                        <select class="form-control-sm form-control" id="modo_internacao_int"
                            name="modo_internacao_int">
                            <option value="">Selecione</option>
                            <option value="Clínica"
                                <?php if ($intern['modo_internacao_int'] == 'Clínica') echo 'selected'; ?>>
                                Clínica</option>
                            <option value="Pediatria"
                                <?php if ($intern['modo_internacao_int'] == 'Pediatria') echo 'selected'; ?>>
                                Pediatria
                            </option>
                            <option value="Ortopedia"
                                <?php if ($intern['modo_internacao_int'] == 'Ortopedia') echo 'selected'; ?>>
                                Ortopedia
                            </option>
                            <option value="Obstetrícia"
                                <?php if ($intern['modo_internacao_int'] == 'Obstetrícia') echo 'selected'; ?>>
                                Obstetrícia
                            </option>
                        </select>
                    </div>

                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="tipo_admissao_int">Tipo Internação</label>
                        <select class="form-control-sm form-control" id="tipo_admissao_int" name="tipo_admissao_int">
                            <option value="">Selecione</option>
                            <option value="Eletiva"
                                <?php if ($intern['tipo_admissao_int'] == 'Eletiva') echo 'selected'; ?>>
                                Eletiva</option>
                            <option value="Urgência"
                                <?php if ($intern['tipo_admissao_int'] == 'Urgência') echo 'selected'; ?>>
                                Urgência</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-3 mb-2">
                        <label class="control-label" for="fk_cid_int">CID</label>
                        <select class="form-control selectpicker show-tick" data-size="5" id="fk_cid_int"
                            name="fk_cid_int" data-live-search="true">
                            <option value="">Selecione o CID</option>

                            <?php foreach ($cids as $cid): ?>
                            <?php $idCid = (int)$cid['id_cid']; ?>
                            <option value="<?= $idCid ?>" <?= ($cidSelecionado == $idCid) ? 'selected' : '' ?>>
                                <?= $cid['cat'] . " - " . $cid["descricao"] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="grupo_patologia_int">Grupo Patologia</label>
                        <select class="form-control-sm form-control" id="grupo_patologia_int"
                            name="grupo_patologia_int">
                            <option value="">Selecione</option>
                            <?php foreach ($dados_grupo_pat as $grupo): ?>
                            <option value="<?= $grupo ?>"
                                <?= ($grupo == $intern['grupo_patologia_int']) ? 'selected' : ''; ?>>
                                <?= $grupo ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-2 mb-2">
                        <label class="control-label" for="origem_int">Origem</label>
                        <select class="form-control-sm form-control" id="origem_int" name="origem_int">
                            <option value="">Selecione</option>
                            <?php foreach ($origem as $origens): ?>
                            <option value="<?= $origens ?>"
                                <?= ($origens == $intern['origem_int']) ? 'selected' : ''; ?>>
                                <?= $origens ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-1 mb-2">
                        <label for="senha_int">Senha</label>
                        <input type="text" maxlength="20" class="form-control form-control-sm" id="senha_int"
                            value="<?= $intern["senha_int"] ?>" name="senha_int">
                    </div>
                </div>
                <div class="form-group row">
                    <div style="display: <?= ($intern['int_pertinente_int'] !== '') ? 'block' : 'none'; ?>"
                        id="div_int_pertinente_int" class="form-group col-sm-2">
                        <label class="control-label" for="int_pertinente_int"><span style="color: red;">*</span>
                            Internação
                            pertinente?</label>
                        <select class="form-control-sm form-control" id="int_pertinente_int" name="int_pertinente_int">
                            <option value="">Selecione</option>
                            <option value="s" <?= ($intern['int_pertinente_int'] == 's') ? 'selected' : ''; ?>>Sim
                            </option>
                            <option value="n" <?= ($intern['int_pertinente_int'] == 'n') ? 'selected' : ''; ?>>Não
                            </option>
                        </select>
                    </div>
                    <div id="div_rel_pertinente_int"
                        style="display: <?= ($intern['int_pertinente_int'] == 'n') ? 'block' : 'none'; ?>"
                        class="form-group col-sm-8">
                        <label for="rel_pertinente_int">Justifique não pertinência</label>
                        <textarea type="textarea" style="resize:none" rows="3" class="form-control"
                            id="rel_pertinente_int"
                            name="rel_pertinente_int"><?= $intern['rel_pertinente_int']; ?></textarea>
                    </div>
                </div>

                <?php
                $antecedentes = $antecedentes ?? [];         // se vier null vira array vazio

                if ($antecedentes) {                         // só ordena se houver itens
                    usort(
                        $antecedentes,
                        fn($a, $b) => strcmp($a['antecedente_ant'], $b['antecedente_ant'])
                    );
                }

                ?>
                <div>
                    <br>
                </div>
                <div class="form-group" style="margin-left:0px; margin-top:-15px">
                    <div>
                        <label for="rel_int">Relatório de Auditoria</label>
                        <textarea id="rel_int" name="rel_int" maxlength="5000" class="form-control" style="resize:none"
                            rows="2" onclick="aumentarText('rel_int')" onblur="reduzirText('rel_int', 2)"><?= htmlspecialchars($intern['rel_int'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
</textarea>
                    </div>

                    <div style="margin-top: 10px;">
                        <label for="acoes_int">Ações da Auditoria</label>
                        <textarea id="acoes_int" name="acoes_int" rows="2" maxlength="5000" class="form-control"
                            style="resize:none" onclick="aumentarText('acoes_int')"
                            onblur="reduzirText('acoes_int', 2)"><?= htmlspecialchars($intern['acoes_int']); ?></textarea>
                    </div>


                    <div style="margin-top: 10px;">
                        <label for="programacao_int">Programação Terapêutica</label>
                        <textarea type="textarea" style="resize:none" maxlength="5000" rows="2"
                            onclick="aumentarText('programacao_int')" onblur="reduzirText('programacao_int', 2)"
                            class="form-control" id="programacao_int"
                            name="programacao_int"><?= htmlspecialchars($intern['programacao_int']); ?></textarea>
                    </div>

                    <div><br></div>
                    <!--****************************************-->
                    <!--************ div de detalhes ***********-->
                    <!--****************************************-->
                    <!-- <input type="text" class="form-control" id="select_detalhes" name="select_detalhes"> -->
                    <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes" value="n">

                    <?php if (!empty($detalhesDaInt[0]['id_detalhes'])): ?>
                    <input type="hidden" name="id_detalhes" value="<?= $detalhesDaInt[0]['id_detalhes'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="fk_int_det" value="<?= $intern['id_internacao'] ?>">
                    <div>
                        <hr>
                    </div>
                    <div class="form-group col-sm-2" style=" margin-top:15px">
                        <label class="control-label" style="font-weight: bold;" for="relatorio-detalhado">Relatório
                            detalhado</label>
                        <select class="form-control-sm form-control" id="relatorio-detalhado"
                            name="relatorio-detalhado">
                            <option value="s">Sim</option>
                            <option value="n" selected>Não</option>
                        </select>

                    </div>
                    <div class="form-group col-sm-3">
                        <?php $agora = date('Y-m-d'); ?> <input type="hidden" id="data_create_int"
                            value='<?= $agora; ?>' name="data_create_int">
                    </div>
                    <div>
                        <hr>
                    </div>
                </div>
                <div id="div-detalhado" class="form-group row" style="margin-left:5px; display:none;">
                    <div class="form-group row">

                        <?php
                        // Valor que veio do banco para este campo
                        $curativo = isset($detalhesDaInt[0]['curativo_det']) ? $detalhesDaInt[0]['curativo_det'] : '';
                        ?>
                        <div class="form-group col-sm-2">
                            <label class="control-label" for="curativo_det">Curativo</label>
                            <select class="form-control-sm form-control" id="curativo_det" name="curativo_det">
                                <option value="">Selecione</option>
                                <option value="s" <?= $curativo === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $curativo === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>
                        <?php $dietaSelecionada = $detalhesDaInt[0]['dieta_det'] ?? '';
                        ?>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="dieta_det">Tipo dieta</label>

                            <select class="form-control-sm form-control" id="dieta_det" name="dieta_det">
                                <option value="">Selecione</option>

                                <?php foreach ($tipos_dieta as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>"
                                    <?= $tipo === $dietaSelecionada ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php
                        $nivelConsc = $detalhesDaInt[0]['nivel_consc_det'] ?? '';
                        ?>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="nivel_consc_det">Nível de Consciência</label>
                            <select class="form-control-sm form-control" id="nivel_consc_det" name="nivel_consc_det">
                                <option value="">Selecione</option>
                                <?php foreach ($opcoes_nivel_consc as $opcao): ?>
                                <option value="<?= htmlspecialchars($opcao) ?>"
                                    <?= $opcao === $nivelConsc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcao) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php
                        $oxigenio = $detalhesDaInt[0]['oxig_det'] ?? '';
                        ?>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="oxig_det">Oxigênio</label>
                            <select class="form-control-sm form-control" id="oxig_det" name="oxig_det">
                                <option value="">Selecione</option>
                                <?php foreach ($opcoes_oxigenio as $opcao): ?>
                                <option value="<?= htmlspecialchars($opcao) ?>"
                                    <?= $opcao === $oxigenio ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcao) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php
                        $oxigenioUso = $detalhesDaInt[0]['oxig_uso_det'] ?? '';
                        ?>

                        <div id="div-oxig" class="form-group col-sm-1">
                            <label class="control-label" for="oxig_uso_det">Lts O2</label>
                            <input class="form-control-sm form-control" type="text" name="oxig_uso_det"
                                id="oxig_uso_det" value="<?= htmlspecialchars($oxigenioUso) ?>">
                        </div>

                        <style>

                        </style>
                        <div class="form-group col-sm-3">
                            <label class="control-label">Dispositivos</label>
                            <div class="d-flex flex-wrap align-items-center">

                                <?php
                                $tqt = $detalhesDaInt[0]['tqt_det'] ?? '';
                                $svd   = $detalhesDaInt[0]['svd_det']   ?? '';
                                $sne   = $detalhesDaInt[0]['sne_det']   ?? '';
                                $gtt   = $detalhesDaInt[0]['gtt_det']   ?? '';
                                $dreno = $detalhesDaInt[0]['dreno_det'] ?? '';
                                ?>

                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="tqt_det">TQT</label>
                                    <input class="form-check-input" type="checkbox" name="tqt_det" id="tqt_det"
                                        value="TQT" <?= $tqt === 'TQT' ? 'checked' : '' ?>>
                                </div>

                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="svd_det">SVD</label>
                                    <input class="form-check-input" type="checkbox" name="svd_det" id="svd_det"
                                        value="SVD" <?= $svd === 'SVD' ? 'checked' : '' ?>>
                                </div>

                                <div class="form-check" style="text-align: center;">
                                    <label style="margin-left:-30px" class="control-label" for="sne_det">SNE</label>
                                    <input class="form-check-input" type="checkbox" name="sne_det" id="sne_det"
                                        value="SNE" <?= $sne === 'SNE' ? 'checked' : '' ?>>
                                </div>

                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="gtt_det">GTT</label>
                                    <input class="form-check-input" type="checkbox" name="gtt_det" id="gtt_det"
                                        value="GTT" <?= $gtt === 'GTT' ? 'checked' : '' ?>>
                                </div>

                                <div class="form-check">
                                    <label style="margin-left:-30px" class="control-label" for="dreno_det">Dreno</label>
                                    <input class="form-check-input" type="checkbox" name="dreno_det" id="dreno_det"
                                        value="Dreno" <?= $dreno === 'Dreno' ? 'checked' : '' ?>>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <?php
                        $dados = $detalhesDaInt[0] ?? [];

                        $val = function ($campo) use ($dados) {
                            return htmlspecialchars($dados[$campo] ?? '');
                        };
                        ?>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="hemoderivados_det">Hemoderivados</label>
                            <select class="form-control-sm form-control" id="hemoderivados_det"
                                name="hemoderivados_det">
                                <option value="">Selecione</option>
                                <option value="s" <?= $val('hemoderivados_det') === 's' ? 'selected' : '' ?>>Sim
                                </option>
                                <option value="n" <?= $val('hemoderivados_det') === 'n' ? 'selected' : '' ?>>Não
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="dialise_det">Diálise</label>
                            <select class="form-control-sm form-control" id="dialise_det" name="dialise_det">
                                <option value="">Selecione</option>
                                <option value="s" <?= $val('dialise_det') === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $val('dialise_det') === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-2">
                            <label class="control-label" for="oxigenio_hiperbarica_det">Oxigenioterapia
                                Hiperbárica</label>
                            <select class="form-control-sm form-control" id="oxigenio_hiperbarica_det"
                                name="oxigenio_hiperbarica_det">
                                <option value="">Selecione</option>
                                <option value="s" <?= $val('oxigenio_hiperbarica_det') === 's' ? 'selected' : '' ?>>Sim
                                </option>
                                <option value="n" <?= $val('oxigenio_hiperbarica_det') === 'n' ? 'selected' : '' ?>>Não
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="qt_det">QT</label>
                            <select class="form-control-sm form-control" id="qt_det" name="qt_det">
                                <option value=""></option>
                                <option value="s" <?= $val('qt_det') === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $val('qt_det') === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="rt_det">RT</label>
                            <select class="form-control-sm form-control" id="rt_det" name="rt_det">
                                <option value=""></option>
                                <option value="s" <?= $val('rt_det') === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $val('rt_det') === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="acamado_det">Acamado</label>
                            <select class="form-control-sm form-control" id="acamado_det" name="acamado_det">
                                <option value=""></option>
                                <option value="s" <?= $val('acamado_det') === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $val('acamado_det') === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="atb_det">Antibiótico</label>
                            <select class="form-control-sm form-control" id="atb_det" name="atb_det">
                                <option value=""></option>
                                <option value="s" <?= $val('atb_det') === 's' ? 'selected' : '' ?>>Sim</option>
                                <option value="n" <?= $val('atb_det') === 'n' ? 'selected' : '' ?>>Não</option>
                            </select>
                        </div>

                        <div id="atb" class="form-group col-sm-3">
                            <label class="control-label" for="atb_uso_det">Antibiótico em uso</label>
                            <input class="form-control" type="text" name="atb_uso_det" id="atb_uso_det"
                                value="<?= $val('atb_uso_det') ?>">
                        </div>

                        <div class="form-group col-sm-1">
                            <label class="control-label" for="medic_alto_custo_det">Medicação</label>
                            <select class="form-control-sm form-control" id="medic_alto_custo_det"
                                name="medic_alto_custo_det">
                                <option value="n" <?= $val('medic_alto_custo_det') === 'n' ? 'selected' : '' ?>>Não
                                </option>
                                <option value="s" <?= $val('medic_alto_custo_det') === 's' ? 'selected' : '' ?>>Sim
                                </option>
                            </select>
                        </div>

                        <div id="medicacaoDet" class="form-group col-sm-3">
                            <label class="control-label" for="qual_medicamento_det">Medicação alto custo</label>
                            <input class="form-control-sm form-control" type="text" name="qual_medicamento_det"
                                id="qual_medicamento_det" value="<?= $val('qual_medicamento_det') ?>">
                        </div>

                        <?php
                        $exames = htmlspecialchars($detalhesDaInt[0]['exames_det'] ?? '');
                        $oportunidades = htmlspecialchars($detalhesDaInt[0]['oportunidades_det'] ?? '');
                        ?>

                        <div>
                            <label for="exames_det">Exames relevantes</label>
                            <textarea type="textarea" style="resize:none" maxlength="5000" rows="3"
                                onclick="aumentarText('exames_det')" onblur="reduzirText('exames_det', 3)"
                                class="form-control" id="exames_det" name="exames_det"><?= $exames ?></textarea>
                        </div>

                        <div>
                            <label for="oportunidades_det">Oportunidades</label>
                            <textarea type="textarea" style="resize:none" maxlength="5000" rows="2"
                                onclick="aumentarText('oportunidades_det')" onblur="reduzirText('oportunidades_det', 3)"
                                class="form-control" id="oportunidades_det"
                                name="oportunidades_det"><?= $oportunidades ?></textarea>
                        </div>

                    </div>

                    <div class="form-group row">
                        <?php
                        $dados = $detalhesDaInt[0] ?? [];

                        $val = function ($campo) use ($dados) {
                            return htmlspecialchars($dados[$campo] ?? '');
                        };
                        ?>

                        <div class="form-group col-sm-3">
                            <label class="control-label" for="liminar_det">Possui Liminar?</label>
                            <select class="form-control-sm form-control" id="liminar_det" name="liminar_det">
                                <option value="n" <?= $val('liminar_det') === 'n' ? 'selected' : '' ?>>Não</option>
                                <option value="s" <?= $val('liminar_det') === 's' ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-3">
                            <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                            <select class="form-control-sm form-control" id="paliativos_det" name="paliativos_det">
                                <option value="n" <?= $val('paliativos_det') === 'n' ? 'selected' : '' ?>>Não</option>
                                <option value="s" <?= $val('paliativos_det') === 's' ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-3">
                            <label class="control-label" for="parto_det">Parto</label>
                            <select class="form-control-sm form-control" id="parto_det" name="parto_det">
                                <option value="n" <?= $val('parto_det') === 'n' ? 'selected' : '' ?>>Não</option>
                                <option value="s" <?= $val('parto_det') === 's' ? 'selected' : '' ?>>Sim</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-3">
                            <label class="control-label" for="braden_det">Escala de Braden</label>
                            <select class="form-control-sm form-control" id="braden_det" name="braden_det">
                                <option value=""></option>
                                <option value="alto" <?= $val('braden_det') === 'alto' ? 'selected' : '' ?>>Alto
                                </option>
                                <option value="moderado" <?= $val('braden_det') === 'moderado' ? 'selected' : '' ?>>
                                    Moderado
                                </option>
                                <option value="baixo" <?= $val('braden_det') === 'baixo' ? 'selected' : '' ?>>Baixo
                                </option>
                            </select>
                        </div>

                    </div>
                    <div>
                        <hr>
                    </div>
                </div>

                <!-- Accordion com toggle individual em Bootstrap 5 -->
                <div class="accordion" id="accordionInternacao">
                    <!-- 1) TUSS -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTuss">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTuss" aria-expanded="false" aria-controls="collapseTuss">
                                <i class="fa-solid fa-notes-medical me-2"></i>
                                <span>TUSS</span>
                            </button>
                        </h2>
                        <div id="collapseTuss" class="accordion-collapse collapse" aria-labelledby="headingTuss">
                            <div class="accordion-body">
                                <?php include_once('formularios/form_edit_internacao_tuss2.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- 2) UTI -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingUti">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseUti" aria-expanded="false" aria-controls="collapseUti">
                                <i class="fa-solid fa-procedures me-2"></i>
                                <span>Editar UTI</span>
                            </button>
                        </h2>
                        <div id="collapseUti" class="accordion-collapse collapse" aria-labelledby="headingUti">
                            <div class="accordion-body">
                                <?php include_once('formularios/form_edit_internacao_uti2.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- 3) GESTÃO -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingGestao">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseGestao" aria-expanded="false" aria-controls="collapseGestao">
                                <i class="fa-solid fa-wallet me-2"></i>
                                <span>Editar Gestão</span>
                            </button>
                        </h2>
                        <div id="collapseGestao" class="accordion-collapse collapse" aria-labelledby="headingGestao">
                            <div class="accordion-body">
                                <?php include_once('formularios/form_edit_internacao_gestao2.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- 4) PRORROGAÇÕES -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingProrrog">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseProrrog" aria-expanded="false" aria-controls="collapseProrrog">
                                <i class="fa-solid fa-calendar-alt me-2"></i>
                                <span>Editar Prorrogações</span>
                            </button>
                        </h2>
                        <div id="collapseProrrog" class="accordion-collapse collapse" aria-labelledby="headingProrrog">
                            <div class="accordion-body">
                                <?php include_once('formularios/form_edit_internacao_prorrog2.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- 5) NEGOCIAÇÕES -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingNegoc">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseNegoc" aria-expanded="false" aria-controls="collapseNegoc">
                                <i class="fa-solid fa-handshake me-2"></i>
                                <span>Editar Negociações</span>
                            </button>
                        </h2>
                        <div id="collapseNegoc" class="accordion-collapse collapse" aria-labelledby="headingNegoc">
                            <div class="accordion-body">
                                <?php include_once('formularios/form_edit_internacao_negoc2.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>


                <br>
                <button type="submit" class="btn btn-success"><i style="font-size: 1rem;margin-right:5px;" value="edit"
                        class="fa-solid fa-check edit-icon"></i>Atualizar</button>

            </form>
        </div>

        <!-- <div class="row">
            <div class="form-group col-md-6">
                <label for="intern_files">Arquivos</label>
                <input type="file" class="form-control" name="intern_files[]" id="intern_files"
                    accept="image/png, image/jpeg" multiple>
                <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
            </div>
        </div> -->


        </form>
    </div>

    <script>
// Função para aumentar o tamanho do campo de texto do relatório de auditoria
function aumentarText(textareaId) {
    document.getElementById(textareaId).rows = 20;
}

function reduzirText(textareaId, originalRows) {
    document.getElementById(textareaId).rows = originalRows;
}
    </script>
    <script>
$(document).ready(function() {
    // Verifica se a função existe antes de chamar
    if (typeof $.fn.selectpicker === 'function') {
        $('.selectpicker').selectpicker();
        // Listener para quando carregar
        $('.selectpicker').on('loaded.bs.select', function() {
            $('.bs-searchbox input').attr('placeholder', 'Digite para pesquisar...');
        });
    }
});
    </script>

    <!-- <script src="js/scriptDataInt.js"></script> -->
    <script src="<?= $BASE_URL ?>js/text_cad_internacao.js"></script>
    <script src="js/select_internacao.js"></script>

    <script>
// mostrar div de uti caso alterar acaomodacao int para UTI
document.getElementById("acomodacao_int").addEventListener("change", function() {
    var divUti = document.querySelector("#container-uti");
    if (this.value === "UTI") {
        divUti.style.display = "block";
    } else {
        divUti.style.display = "none";
    }
});
let pacienteStatus = null; // Variável global para armazenar o status do paciente

function teste() {
    event.preventDefault(); //prevent default action 
    let post_url = "check_internacao.php"; //get form action url
    let request_method = "POST"; //get form GET/POST method
    var paciente = document.querySelector("#fk_paciente_int").value;
    $.ajax({
        url: post_url,
        type: request_method,
        data: {
            id_paciente: paciente
        },
        success: function(result) {

            var alert_div = document.getElementById('alert_intern');
            if (result == 1) {
                alert_div.style.display = "block";
            } else {
                alert_div.style.display = "none";

            }
        }
    })
}

var dialogResult = false;


document.getElementById("data_intern_int").addEventListener("blur", function() {
    const input = this;
    const dataInternacao = new Date(input.value);
    const dataHoje = new Date();
    const erroDiv = document.getElementById("erro-data-internacao");

    erroDiv.style.display = "none";
    erroDiv.textContent = "";

    if (!input.value) return;

    const dataFormatadaHoje = dataHoje.toISOString().split("T")[0];
    const dataFormatadaInput = input.value;

    // Caso a data seja futura
    if (dataFormatadaInput > dataFormatadaHoje) {
        erroDiv.textContent = "A data da internação não pode ser maior que a data atual.";
        erroDiv.style.display = "block";
        input.value = "";

        setTimeout(() => {
            erroDiv.style.display = "none";
            erroDiv.textContent = "";
        }, 5000);
        return;
    }

    // Verifica se a data está mais de 30 dias no passado
    const diffEmMilissegundos = dataHoje - dataInternacao;
    const diffDias = diffEmMilissegundos / (1000 * 60 * 60 * 24);

    if (diffDias > 30) {
        erroDiv.textContent = "Deseja prorrogar acima de 30 dias?";
        erroDiv.style.display = "block";

        setTimeout(() => {
            erroDiv.style.display = "none";
            erroDiv.textContent = "";
        }, 7000);
    }
});
    </script>

    <script>
$(document).ready(function() {
    // Evento de mudança para o hospital selecionado
    $('#hospital_selected').on('change', function() {

        const id_hospital = $(this).val(); // Captura o ID do hospital selecionado

        if (!id_hospital) {
            return;
        }

        // Solicitação AJAX para buscar dados filtrados
        fetchAcomodacoes(id_hospital);
    });

    // Função para realizar a requisição AJAX e preencher os selects
    function fetchAcomodacoes(id_hospital) {
        $.ajax({
            url: 'process_acomodacao.php', // Endereço do script no servidor
            type: 'POST',
            dataType: 'json',
            data: {
                id_hospital
            }, // Dados enviados ao servidor
            beforeSend: function() {

            },
            success: function(response) {

                if (response.status === 'success') {
                    const acomodacoes = response.acomodacoes;

                    // Atualiza os selects "troca_de" e "troca_para"
                    populateSelects(acomodacoes);
                } else {
                    console.error("Erro recebido do servidor:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro na requisição AJAX:", error);
                console.error("Status:", status);
                console.error("Resposta completa:", xhr.responseText);
            },
        });
    }


    // Função para popular os selects "troca_de" e "troca_para" com as acomodações recebidas
    function populateSelects(acomodacoes) {
        let options = '<option value="">Selecione a Acomodação</option>';
        acomodacoes.forEach(ac => {
            options +=
                `<option value="${ac.id_acomodacao}-${ac.acomodacao_aco}" data-valor="${ac.valor_aco}">${ac.acomodacao_aco}</option>`;
        });

        // Atualiza os selects com as novas opções
        $('select[name="troca_de"]').html(options);
        $('select[name="troca_para"]').html(options);

        // Limpa os campos relacionados
        $('input[name="saving"]').val('');
        $('input[name="qtd"]').val('');
        $('input[name="saving_show"]').val('').css('color', '');
    }

    // Função para calcular savings ao alterar os selects ou a quantidade
    $(document).on('change keyup',
        'select[name="troca_de"], select[name="troca_para"], input[name="qtd"]',
        function() {
            calculateSavings($(this).closest('.negotiation-field-container'));
        });

    function carregarValoresTroca(container) {
        // Pega os valores selecionados dos selects
        const trocaDeOption = container.find('select[name="troca_de"] option:selected');
        const trocaParaOption = container.find('select[name="troca_para"] option:selected');

        // Extrai os valores do atributo 'data-valor'
        const trocaDe = parseFloat(trocaDeOption.data('valor')) || 0;
        const trocaPara = parseFloat(trocaParaOption.data('valor')) || 0;

        // Carrega os valores nos inputs correspondentes
        container.find('input[name="troca_de"]').val(trocaDe);
        container.find('input[name="troca_para"]').val(trocaPara);

    }

    // Função para calcular e atualizar os campos de savings
    function calculateSavings(container) {
        // Pega os selects selecionados
        const trocaDeOption = container.find('select[name="troca_de"] option:selected');
        const trocaParaOption = container.find('select[name="troca_para"] option:selected');
        const quantidadeInput = container.find('input[name="qtd"]');

        // Extraímos o valor correto do atributo 'data-valor'
        const trocaDeValor = parseFloat(trocaDeOption.attr('data-valor')) || 0;
        const trocaParaValor = parseFloat(trocaParaOption.attr('data-valor')) || 0;
        const quantidade = parseInt(quantidadeInput.val(), 10) || 0;

        // Se algum valor estiver inválido, apenas limpamos o campo e saímos
        if (isNaN(trocaDeValor) || isNaN(trocaParaValor) || isNaN(quantidade)) {
            container.find('input[name="saving"]').val('');
            container.find('input[name="saving_show"]').val('').css('color', '');
            return;
        }

        // Cálculo correto do saving
        const saving = (trocaDeValor - trocaParaValor) * quantidade;

        // Atualiza os campos de saving com o formato correto
        container.find('input[name="saving"]').val(saving.toFixed(2));
        container.find('input[name="saving_show"]').val(
            saving >= 0 ? `R$ ${saving.toFixed(2)}` : `-R$ ${Math.abs(saving).toFixed(2)}`
        ).css('color', saving >= 0 ? 'green' : 'red');
    }

});




// Exibe o container apenas quando select_prorrog for "s"
document.addEventListener("DOMContentLoaded", function() {
    const selectProrrog = document.getElementById("select_prorrog");
    const containerProrrog = document.getElementById("container-prorrog");

    if (selectProrrog) {
        selectProrrog.addEventListener("change", function() {
            if (this.value === "s") {
                containerProrrog.style.display = "block";
            } else {
                containerProrrog.style.display = "none";
            }
        });

        // Verifica o valor inicial
        if (selectProrrog.value === "s") {
            containerProrrog.style.display = "block";
        } else {
            containerProrrog.style.display = "none";
        }
    }
});
    </script>

    <script>
document.getElementById("data_visita_int").addEventListener("change", function() {
    const dataInternacao = new Date(document.getElementById("data_intern_int").value);
    const dataVisita = new Date(this.value);
    const hoje = new Date();
    const seteDiasDepois = new Date();
    seteDiasDepois.setDate(hoje.getDate() + 7);

    const errorMessage = document.getElementById("error-message");

    // Reseta a mensagem de erro
    errorMessage.style.display = "none";
    errorMessage.textContent = "";

    // Validações
    if (dataVisita < dataInternacao) {
        errorMessage.textContent = "A data da visita não pode ser menor que a data de internação.";
        errorMessage.style.display = "block";
    } else if (dataVisita > seteDiasDepois) {
        errorMessage.textContent = "A data da visita não pode ser maior que 7 dias da data atual.";
        errorMessage.style.display = "block";
    }
});

// internacao pertinente
document.getElementById("tipo_admissao_int").addEventListener("change", function() {
    const tipoAdmissao = this.value;
    const divPertinente = document.getElementById("div_int_pertinente_int");
    const divRelPertinente = document.getElementById("div_rel_pertinente_int");

    // Resetando a visibilidade
    divPertinente.style.display = "none";
    divRelPertinente.style.display = "none";

    if (tipoAdmissao === "Urgência") {
        divPertinente.style.display = "block";

        document.getElementById("int_pertinente_int").addEventListener("change", function() {
            const intPertinente = this.value;

            if (intPertinente === "n") {
                divRelPertinente.style.display = "block";
            } else {
                divRelPertinente.style.display = "none";
            }
        });
    }
});

document.querySelector("form").addEventListener("submit", function(event) {
    generateNegotiationsJSON(); // Gera o JSON antes do envio

    // Remove os campos individuais antes de enviar o formulário
    const inputsToDisable = document.querySelectorAll(
        'input[name="troca_de"], input[name="troca_para"], input[name="qtd"], input[name="saving"]'
    );
    inputsToDisable.forEach((input) => input.disabled = true);
});


//criar o json de antecedentes
document.getElementById('fk_patologia2').addEventListener('change', function() {
    const selectedOptions = Array.from(this.selectedOptions).map(option => parseInt(option.value,
        10)); // Converte os valores para inteiros
    const fkPaciente = parseInt(document.getElementById('fk_paciente_int').value,
        10); // Garante que fkPaciente é inteiro
    const fkInternacao = parseInt(document.getElementById('id_internacao').value,
        10); // Garante que fkInternacao é inteiro

    const jsonAntecedentes = selectedOptions.map(idAntecedente => ({
        fk_id_paciente: fkPaciente,
        fk_internacao_ant_int: fkInternacao + 1, // Soma 1 ao valor de fkInternacao
        intern_antec_ant_int: idAntecedente // Certifica que idAntecedente é um número inteiro
    }));

    // Atualiza o campo hidden com o JSON gerado
    document.getElementById('json-antec').value = JSON.stringify(jsonAntecedentes);
});
    </script>

    <style>
/* coloca no seu <head> ou no final do CSS carregado */
.accordion .accordion-button {
    background-color: #5e2363;
    color: #fff;
}

.accordion .accordion-button:not(.collapsed) {
    background-color: #5e2363;
    color: #fff;
}

/* inverte a cor do ícone gerado pelo ::after */
.accordion .accordion-button::after {
    filter: brightness(0) invert(1);
}

/* remove o foco escuro padrão */
.accordion .accordion-button:focus {
    box-shadow: none;
}
    </style>
