<div class="row">
    <?php
    // calculo de dias da internacao e dias da ultima visita
    $hoje = date('Y-m-d');
    $visitaAnt = date("Y-m-d", strtotime($ultimaVis['data_visita_vis']));
    $intern = date("Y-m-d", strtotime($ultimaVis['data_intern_int']));
    $atual = new DateTime($hoje);
    $visAnt = new DateTime($visitaAnt);
    $dataIntern = new DateTime($intern);

    $intervaloUltimaVis = $visAnt->diff($atual);
    $diasIntern = $dataIntern->diff($atual);

    // print_r($id_internacao);
    $visitasDAO = new visitaDAO($conn, $BASE_URL);
    $internacaoDAO = new internacaoDAO($conn, $BASE_URL);
    // $queryVis = new internacaoDAO($conn, $BASE_URL);
    $query2DAO = new visitaDAO($conn, $BASE_URL);
    $id_internacao = filter_input(INPUT_GET, "id_internacao", FILTER_SANITIZE_NUMBER_INT);
    $visitas = $visitasDAO->joinVisitaInternacao($id_internacao);

    ?><h4 class="page-title">Cadastrar visita</h4>
    <p class="page-description">Adicione informações sobre a visita</p>

    <div id="view-contact-container" class="container-fluid" style="align-items:center">
        <hr>
        <span style="font-weight: 500; margin:0px 5px 0px 40px ">Internação:</span>
        <span style="font-weight: 800; margin:0px 80px 0px 5px "><?= $internacaoList['0']['id_internacao'] ?></span>
        <span class="card-title bold" style="font-weight: 500; margin:0px 5px 0px 20px">Hospital:</span>
        <span class="card-title bold"
            style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoList['0']['nome_hosp'] ?></span>
        <span style="font-weight: 500; margin:0px 5px 0px 80px">Paciente:</span>
        <span style=" font-weight: 800; margin:0px 10px 0px 0px"><?= $internacaoList['0']['nome_pac'] ?></span>
        <span style="font-weight: 500; margin:0px 5px 0px 80px">Data internação:</span>
        <span
            style="font-weight: 800; margin:0px 80px 0px 0px"><?= date("d/m/Y", strtotime($internacaoList['0']['data_intern_int'])); ?></span>
        <hr>
    </div>

    <form class="formulario" action="<?= $BASE_URL ?>process_visita.php" id="add-visita-form" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="type" value="create">
        <?php
        $contarVis = 0; //contar numero de visitas por internacao 
        $queryVis = $internacaoDAO->selectInternVis($id_internacao);
        foreach ($queryVis as $item) {
            $contarVis++;
        };
        ?>
        <div class="form-group row">
            <div class="form-group col-sm-1">
                <label style="text-align:center" for="visita_no_vis"> Visita No.</label>
                <input type="text" readonly style="text-align:center; font-weight:800" value="<?= $contarVis + 1 ?>"
                    class="form-control" id="visita_no_vis" name="visita_no_vis">
            </div>
            <div class="form-group col-sm-4">
                <input type="hidden" class="form-control" class="form-control" id="usuario_create"
                    value="<?= $_SESSION['email_user'] ?>" name="usuario_create">
            </div>
            <div class="form-group col-sm-4">
                <input type="hidden" class="form-control" value="<?= $id_internacao ?>" id="fk_internacao_vis"
                    name="fk_internacao_vis" placeholder="">
            </div>

            <div>
                <label for="rel_visita_vis">Relatório Auditoria</label>
                <textarea type="textarea" rows="2" onclick="aumentarTextAudit()" class="form-control"
                    id="rel_visita_vis" name="rel_visita_vis"></textarea>
            </div>
            <div style="margin-bottom:20px">
                <label for="acoes_int_vis">Ações Auditoria</label>
                <textarea type="textarea" rows="2" onclick="aumentarTextAcoes()" class="form-control" id="acoes_int_vis"
                    name="acoes_int_vis" placeholder="Ações de auditoria"></textarea>
            </div>
            <hr>
            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="internado_uti_int" name="internado_uti_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="internacao_uti_int" name="internacao_uti_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="conta_finalizada_int"
                    name="conta_finalizada_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="n" id="conta_paga_int" name="conta_paga_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="s" id="internacao_ativa_int"
                    name="internacao_ativa_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="<?= ($_SESSION['id_usuario']) ?>" id="fk_usuario_vis"
                    name="fk_usuario_vis">
            </div>
            <div class="form-group col-sm-1">

                <input type="hidden" class="form-control" value="<?= ($_SESSION['cargo']) ?>" id="fk_usuario_vis"
                    name="fk_usuario_vis">
            </div>
            <div class="form-group col-sm-2">
                <?php
                $agora = date('d-m-Y');
                $agoraLanc = date('Y-m-d');
                ?>
                <input type="hidden" value=' <?= $agora; ?>' class="form-control" id="data_visita_vis"
                    name="data_visita_vis">
            </div>
            <div class="form-group col-sm-3">
                <label for="data_lancamento_vis">Data do lançamento</label>
                <input type="date" class="form-control" id="data_lancamento_vis"
                    name="data_lancamento_vis" value="<?= $agoraLanc; ?>" readonly tabindex="-1"
                    onfocus="this.blur();" onkeydown="return false;" style="cursor:not-allowed;">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_enf_vis" name="visita_enf_vis"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                        echo 's';
                                                                                                                    } else {
                                                                                                                        echo 'n';
                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                        echo 's';
                                                                                                                                    } else {
                                                                                                                                        echo 'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_med_vis" name="visita_med_vis"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                                        echo 's';
                                                                                                                    } else {
                                                                                                                        echo 'n';
                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                                                        echo 's';
                                                                                                                                    } else {
                                                                                                                                        echo 'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                        echo ($_SESSION['login_user']);
                                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                                        echo ($_SESSION['login_user']);
                                                                                                                                                    }; ?>">
            </div>
            <?php if (($_SESSION['cargo']) === 'Med_auditor') {
            }; ?>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_med" name="visita_auditor_prof_med"
                    placeholder="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                                                        echo ($_SESSION['login_user']);
                                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                                                                        echo ($_SESSION['email_user']);
                                                                                                                                                    }; ?>">
            </div>
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label style="color:blue" class="control-label" for="select_prorrog">Tuss</label>
                    <select class="form-control" id="select_tuss" name="select_tuss">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label style="color:blue" class="control-label" for="select_prorrog">Prorrogação</label>
                    <select class="form-control" id="select_prorrog" name="select_prorrog">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label style="color:blue;" class="control-label" for="select_gestao">Gestão</label>

                    <select class="form-control" id="select_gestao" name="select_gestao">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label style="color:blue" class="control-label" for="select_uti">UTI</label>
                    <select class="form-control" id="select_uti" name="select_uti">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label style="color:blue" class="control-label" for="select_negoc">Negociações</label>
                    <select class="form-control" id="select_negoc" name="select_negoc">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <br>
            </div>
            <!-- FORMULARIO DE GESTÃO -->
            <?php include_once('formularios/form_cad_visita_tuss.php'); ?>
            <!-- FORMULARIO DE GESTÃO -->

            <?php include_once('formularios/form_cad_visita_gestao.php'); ?>

            <!-- FORMULARIO DE UTI -->
            <?php include_once('formularios/form_cad_visita_uti.php'); ?>

            <!-- FORMULARIO DE PRORROGACOES -->
            <?php include_once('formularios/form_cad_visita_prorrog.php'); ?>

            <!-- <FORMULARO DE NEGOCIACOES -->
            <?php include_once('formularios/form_cad_visita_negoc.php'); ?>
            <br>

            <div>
                <button style="margin:10px" type="submit" class="btn-sm btn-success btn-int-niveis">Cadastrar</button>
            </div>
    </form>
</div>
</div>
<hr>
<?php if ($contarVis > 0) { ?>
<div style="margin:0 0px 20px 30px" class="form-group col-sm-3">
    <label id="textVisita" style="font-weight:800" for="exibirVisita"><i style="color:green; font-weight:800"
            class="fas fa-eye check-icon"></i> Visualizar visitas anteriores</label>
    <input style="margin-left:20px" type="checkbox" id="exibirVisita" name="exibirVisita" value="exibirVisita">
    <br>
</div>
<hr>
<?php } ?>
<div id="div-visitas" style="display:none">
    <?php

    if (!$visitas) {
        echo ("<br>");
        echo ("<p style='margin-left:100px'> <b>-- Esta internação ainda não possui visita -- </b></p>");
        echo ("<br>");
    } else { ?>
    <h6 class="page-title">Relatórios anteriores</h6>
    <table class="table table-sm table-striped  table-hover table-condensed">
        <thead>
            <tr>
                <th scope="col" style="width:2%">Visita</th>
                <th scope="col" style="width:2%">Data visita</th>
                <th scope="col" style="width:2%">Med</th>
                <th scope="col" style="width:2%">Enf</th>
                <th scope="col" style="width:15%">Relatório</th>
                <th scope="col" style="width:2%">Visualizar</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $hoje = date('Y-m-d');
                $atual = new DateTime($hoje);
                foreach ($visitas as $intern) :
                ?>
            <tr>
                <td scope="row"><?= $intern["id_visita"] ?></td>
                <td scope="row"><?= $intern["data_visita_vis"] ?></td>
                <td scope="row" class="nome-coluna-table"><?php if ($intern["visita_med_vis"] == "s") { ?><span
                        id="boot-icon" class="bi bi-check"
                        style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                    <?php }; ?></td>
                <td scope="row" class="nome-coluna-table"><?php if ($internacaoList["visita_enf_vis"] == "s") { ?><span
                        id="boot-icon" class="bi bi-check"
                        style="font-size: 1.2rem; font-weight:800; color: rgb(0, 128, 55);"></span>
                    <?php }; ?>
                </td>
                <td scope="row"><?= $intern["rel_visita_vis"] ?></td>
                <td><a href="<?= $BASE_URL ?>show_visita.php?id_visita=<?= $intern["id_visita"] ?>"><i
                            style="color:green; margin-right:10px" class="aparecer-acoes fas fa-eye check-icon"></i></a>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php }; ?>
    <br>
    <hr>
</div>

<script src="js/text_cad_visita.js"></script>
<script src="js/select_visita.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
