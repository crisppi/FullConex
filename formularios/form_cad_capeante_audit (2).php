<?php

$Internacao_geral = new internacaoDAO($conn, $BASE_URL);
$Internacaos = $Internacao_geral->findGeral();

$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$pacientes = $pacienteDao->findGeral($limite, $inicio);

$capeante_geral = new capeanteDAO($conn, $BASE_URL);
$capeante = $capeante_geral->findGeral($limite, $inicio);

$hospital_geral = new HospitalDAO($conn, $BASE_URL);
$hospitals = $hospital_geral->findGeral($limite, $inicio);

$patologiaDao = new patologiaDAO($conn, $BASE_URL);
$patologias = $patologiaDao->findGeral();

$internacao_geral = new internacaoDAO($conn, $BASE_URL);
$internacao = $internacao_geral->findGeral();

//Instanciando a classe
$QtdTotalInt = new internacaoDAO($conn, $BASE_URL);
// METODO DE BUSCA DE PAGINACAO 
$pesquisa_nome = filter_input(INPUT_GET, 'pesquisa_nome');
$pesqInternado = filter_input(INPUT_GET, 'pesqInternado') ?: "s";
$limite = filter_input(INPUT_GET, 'limite') ? filter_input(INPUT_GET, 'limite') : 10;
$pesquisa_pac = filter_input(INPUT_GET, 'pesquisa_pac');
$id_internacao = filter_input(INPUT_GET, 'id_internacao');
$id_capeante = filter_input(INPUT_GET, 'id_capeante');
$type = filter_input(INPUT_GET, 'type');

$ordenar = filter_input(INPUT_GET, 'ordenar') ? filter_input(INPUT_GET, 'ordenar') : 1;
// $buscaAtivo = in_array($buscaAtivo, ['s', 'n']) ?: "";
$condicoes = [
    strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
];
$condicoes = array_filter($condicoes);
$where = implode(' AND ', $condicoes);
$order = $ordenar;
$obLimite = null;
$dataFech = date('Y-m-d');

if ($type == 'create') {
    $condicoes = [
        strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
        strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,
    ];
    $condicoes = array_filter($condicoes);
    $where = implode(' AND ', $condicoes);

    $intern = $internacao_geral->selectAllInternacaoNewCap($where, $order, $obLimite);
    $parcial_count = $capeante_geral->getCapeanteByInternacao($id_internacao);
    $parcial_date = $capeante_geral->getLastCapeanteByInternacao($id_internacao);
    $intern['0']['parcial_capeante'] = 's';
    $intern['0']['parcial_num'] = $parcial_count['qtd'];
    $intern['0']['data_inicial_capeante'] = $parcial_date['0'];
} else {
    $condicoes = [
        strlen($id_capeante) ? 'id_capeante = "' . $id_capeante . '"' : NULL,
    ];
    $condicoes = array_filter($condicoes);
    $where = implode(' AND ', $condicoes);
    $intern = $internacao_geral->selectAllInternacaoCap($where, $order, $obLimite);
}
extract($intern);
?>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>


<div id='main-container' class="row" style="margin-top:12px;">
    <h4 id="titulo" class="page-title titulo"> Capeante - Lançamento</h4>
    <p id="subtitulo" class="page-description">Adicione informações do Capeante</p>

    <form class="visible" action="<?= $BASE_URL ?>process_capeante.php" id="add-internacao-form" method="POST"
        enctype="multipart/form-data">
        <?php if ($type == "create") { ?>
        <input type="hidden" name="type" value="create">
        <input type="hidden" name="id_capeante" value="<?= null ?>">
        <input type="hidden" id="parcial_date" name="parcial_date" value="<?= $parcial_date['0'] ?>">
        <?php } else { ?>
        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_capeante" value="<?= $intern['0']['id_capeante'] ?>">
        <?php } ?>

        <!-- profissionais  validacao para input-->

        <input type="hidden" class="form-control" id="adm_capeante" name="adm_capeante" value="<?php if ($_SESSION['cargo'] === "Adm")
                                                                                                    echo 's' ?>">
        <input type="hidden" class="form-control" id="aud_enf_capeante" name="aud_enf_capeante"
            value="<?php if ($_SESSION['cargo'] === "Enf_Auditor")
                                                                                                            echo 's' ?>">
        <input type="hidden" class="form-control" id="aud_med_capeante" name="aud_med_capeante"
            value="<?php if ($_SESSION['cargo'] === "Med_auditor")
                                                                                                            echo 's' ?>">

        <!-- preenchar lista com campos do BD conforme profissionais -->
        <input type="hidden" class="form-control" id="adm_check" name="adm_check" value="<?php if ($_SESSION['cargo'] === "Adm") {
                                                                                                echo "s";
                                                                                            } else {
                                                                                                echo ($intern['0']['adm_check']);
                                                                                            };
                                                                                            ?>">

        <input type="hidden" class="form-control" id="med_check" name="med_check" value="<?php if ($_SESSION['cargo'] === "Med_auditor") {
                                                                                                echo "s";
                                                                                            } else {
                                                                                                echo ($intern['0']['med_check']);
                                                                                            };
                                                                                            ?>">
        <input type="hidden" class="form-control" id="enfer_check" name="enfer_check" value="<?php if ($_SESSION['cargo'] === "Enf_Auditor") {
                                                                                                    echo "s";
                                                                                                } else {
                                                                                                    echo ($intern['0']['enfer_check']);
                                                                                                };
                                                                                                ?>">
        <input type="hidden" class="form-control" id="adm_check" name="adm_check" value="<?php if ($_SESSION['cargo'] === "Adm") {
                                                                                                echo "s";
                                                                                            } else {
                                                                                                echo ($intern['0']['adm_check']);
                                                                                            }
                                                                                            ?>">

        <div class="form-group row">
            <div id="view-contact-container" class="container-fluid" style="align-items:center">
                <br>
                <div style="text-align:left">
                    <span class="card-title bold" style="font-weight: 500; margin:0px 5px 0px 0px">Hospital:</span>
                    <span class="card-title bold" style=" font-weight: 800; margin:0px 120px 0px 0px">
                        <?= $intern['0']['nome_hosp'] ?>
                    </span>
                    <span style="font-weight: 500; margin:0px 5px 0px 0px">Paciente:</span>
                    <span style=" font-weight: 800; margin:0px 120px 0px 0px">
                        <?= $intern['0']['nome_pac'] ?>
                    </span>
                    <span style="font-weight: 500; margin:0px 5px 0px 0px">Data internação:</span>
                    <span style="font-weight: 800; margin:0px 10px 0px 0px">
                        <?= date("d/m/Y", strtotime($intern['0']['data_intern_int'])); ?>
                    </span>

                </div>
                <!-- <br> -->
                <span style="font-weight: 500; margin:0px 5px 0px 0px ">Reg Int:</span>
                <span style="font-weight: 500; margin:0px 180px 0px 5px ">
                    <?= $intern['0']['id_internacao'] ?>
                </span>
                <span style="font-weight: 500; margin:0px 5px 0px 0px ">Conta Número:</span>
                <span style="font-weight: 800; margin:0px 80px 0px 5px "><b>
                        <?= $intern['0']['id_capeante'] ?>
                    </b></span>
                <hr>
            </div>
            <input type="hidden" class="form-control" id="fk_int_capeante" name="fk_int_capeante"
                value="<?= $intern['0']['id_internacao'] ?>" placeholder="<?= $intern['0']['id_internacao'] ?>">
            <input type="hidden" class="form-control" id="fk_hospital_int" name="fk_hospital_int"
                value="<?= $intern['0']['nome_hosp'] ?>" placeholder="<?= $intern['0']['nome_hosp'] ?>" readonly>
            <input type="hidden" class="form-control" id="fk_user_cap" name="fk_user_cap"
                value="<?= $_SESSION['id_usuario'] ?>" placeholder="<?= $_SESSION['id_usuario'] ?>">
            <input type="hidden" class="form-control" id="fk_paciente_int" name="fk_paciente_int"
                placeholder="<?= $intern['0']['nome_pac'] ?>" readonly>
            <input type="hidden" class="form-control" id="data_intern_int" name="data_intern_int"
                value="<?= $intern['0']['data_intern_int'] ?>">
            <?php $agora = date('Y-m-d H:i:s'); ?>
            <input type="hidden" class="form-control" id="data_create_cap" value='<?= $agora; ?>' name="data_create_cap"
                placeholder="">
            <input type="hidden" class="form-control" id="usuario_create_cap" value="<?= $_SESSION['email_user'] ?>"
                name="usuario_create_cap" placeholder="Digite o usuário">

            <!-- campos de dados gerais  -->
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="valor_apresentado_capeante">Valor Apresentado</label>
                    <input required type="text" class="form-control form-control-sm dinheiro"
                        id="valor_apresentado_capeante" name="valor_apresentado_capeante"
                        value="<?php echo (number_format($intern['0']['valor_apresentado_capeante'], 2, ',', '.')) ?>">
                </div>
                <div id="div_data_inicial_capeante" class="form-group form-group-sm col-sm-2">
                    <label for="data_inicial_capeante">Data Inicial</label>
                    <input required type="date" class="form-control form-control-sm " id="data_inicial_capeante"
                        name="data_inicial_capeante" value="<?= $intern['0']['data_inicial_capeante'] ?>">
                    <div class="notif-input oculto" id="notif-input">
                        Data inválida !
                    </div>
                </div>
                <div class="form-group col-sm-2">
                    <label for="data_final_capeante">Data Final</label>
                    <input type="date" class="form-control form-control-sm " id="data_final_capeante"
                        name="data_final_capeante" value="<?= $intern['0']['data_final_capeante'] ?>">
                    <div class="notif-input oculto" id="notif-input2">
                        Data inválida !
                    </div>
                </div>
                <div class="form-group col-sm-2">
                    <label for="diarias_capeante">Diárias</label>
                    <input type="text" class="form-control form-control-sm " id="diarias_capeante"
                        name="diarias_capeante" value="<?= $intern['0']['diarias_capeante'] ?>">
                </div>
                <div class="form-group col-sm-2">
                    <label for="data_fech_capeante">Data Fechamento</label>
                    <input type="date" class="form-control form-control-sm " id="data_fech_capeante"
                        value="<?= $dataFech ?>" name="data_fech_capeante">
                </div>
            </div>
            <hr>
            <!-- campo de valores por grupo -->
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="valor_diarias">Valor Diárias</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_diarias"
                        name="valor_diarias"
                        value="<?php if ($intern['0']['valor_diarias'])
                                                                                                                                        echo (number_format($intern['0']['valor_diarias'], 2, ',', '.')) ?>">
                    <p id="notif_valor_diarias" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_matmed">Valor MatMed</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_matmed"
                        name="valor_matmed"
                        value="<?php if ($intern['0']['valor_matmed'])
                                                                                                                                        echo (number_format($intern['0']['valor_matmed'], 2, ',', '.'))  ?>">
                    <p id="notif_valor_matmed" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_oxig">Valor Oxigenioterapia</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_oxig" name="valor_oxig"
                        value="<?php if ($intern['0']['valor_oxig'])
                                                                                                                                    echo (number_format($intern['0']['valor_oxig'], 2, ',', '.')) ?>">
                    <p id="notif_valor_oxig" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_sadt">Valor SADT</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_sadt" name="valor_sadt"
                        value="<?php if ($intern['0']['valor_sadt'])
                                                                                                                                    echo (number_format($intern['0']['valor_sadt'], 2, ',', '.')) ?>">
                    <p id="notif_valor_sadt" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_taxa">Valor Taxas</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_taxa" name="valor_taxa"
                        value="<?php if ($intern['0']['valor_taxa'])
                                                                                                                                    echo (number_format($intern['0']['valor_taxa'], 2, ',', '.')) ?>">
                    <p id="notif_valor_taxa" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_honorarios">Valor Honorários</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_honorarios"
                        name="valor_honorarios"
                        value="<?php if ($intern['0']['valor_honorarios'])
                                                                                                                                                echo (number_format($intern['0']['valor_honorarios'], 2, ',', '.')) ?>">
                    <p id="notif_valor_honorarios" style="display:none; color:red; font-size:.6em; margin-left:10px">
                        Valor
                        Inválido!</p>
                </div>

            </div>
            <br>

            <!-- campos de glosas -->
            <div style="margin-top:-20px" class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="glosa_diarias">Glosa Diárias</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_diarias"
                        name="glosa_diaria"
                        value="<?php if ($intern['0']['glosa_diaria'])
                                                                                                                                        echo (number_format($intern['0']['glosa_diaria'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_diarias" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="glosa_matmed">Glosa MatMed</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_matmed"
                        name="glosa_matmed"
                        value="<?php if ($intern['0']['glosa_matmed'])
                                                                                                                                        echo (number_format($intern['0']['glosa_matmed'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_matmed" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="glosa_oxig">Glosa Oxigenioterapia</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_oxig" name="glosa_oxig"
                        value="<?php if ($intern['0']['glosa_oxig'])
                                                                                                                                    echo (number_format($intern['0']['glosa_oxig'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_oxig" style="display:none; color:red; font-size:.6em; margin-left:10px">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="glosa_sadt">Glosa SADT</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_sadt" name="glosa_sadt"
                        value="<?php if ($intern['0']['glosa_sadt'])
                                                                                                                                    echo (number_format($intern['0']['glosa_sadt'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_sadt" style="display:none; color:red; font-size:.6em; text-align:center">Valor
                        Inválido!</p>

                </div>
                <div class="form-group col-sm-2">
                    <label for="glosa_taxas">Glosa Taxas</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_taxas" name="glosa_taxas"
                        value="<?php if ($intern['0']['glosa_taxas'])
                                                                                                                                    echo (number_format($intern['0']['glosa_taxas'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_taxas" style="display:none; color:red; font-size:.6em; text-align:center">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="glosa_honorarios">Glosa Honorários</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="glosa_honorarios"
                        name="glosa_honorarios"
                        value="<?php if ($intern['0']['glosa_honorarios'])
                                                                                                                                                echo (number_format($intern['0']['glosa_honorarios'], 2, ',', '.')) ?>">
                    <p id="notif_glosa_honorarios" style="display:none; color:red; font-size:.6em; text-align:center">
                        Valor
                        Inválido!</p>
                </div>

            </div>
            <br>

            <!-- campos de glosas por profissional-->
            <div style="margin-top:-20px" class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="valor_glosa_enf">Glosa Enfermagem</label>
                    <input type="text" class="dinheiro form-control-sm form-control" id="valor_glosa_enf"
                        name="valor_glosa_enf"
                        value="<?php if ($intern['0']['valor_glosa_enf'])
                                                                                                                                            echo (number_format($intern['0']['valor_glosa_enf'], 2, ',', '.'));
                                                                                                                                        else {
                                                                                                                                            echo (number_format($intern['0']['valor_glosa_enf'], 2, ',', '.'));
                                                                                                                                        }; ?>">
                    <p class="oculto mensagem_error" id="err_valor_glosa_enf">Digite um número!</p>
                    <p id="notif_glosa_enf" style="display:none; color:red; font-size:.6em; text-align:center">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_glosa_med">Glosa Médica</label>
                    <input type="text" class="form-control form-control-sm dinheiro" id="valor_glosa_med"
                        name="valor_glosa_med"
                        value="<?php if ($intern['0']['valor_glosa_med'])
                                                                                                                                            echo (number_format($intern['0']['valor_glosa_med'], 2, ',', '.'));
                                                                                                                                        else {
                                                                                                                                            echo (number_format($intern['0']['valor_glosa_med'], 2, ',', '.'));
                                                                                                                                        }; ?>">
                    <p id="notif_glosa_med" style="display:none; color:red; font-size:.6em; text-align:center">Valor
                        Inválido!</p>
                </div>

                <div class="form-group col-sm-2">
                    <label for="valor_glosa_total">Glosa Total</label>
                    <input type="text" class="money2 form-control form-control-sm" id="valor_glosa_total"
                        name="valor_glosa_total"
                        value="<?php if ($intern['0']['valor_glosa_total'])
                                                                                                                                                echo (number_format($intern['0']['valor_glosa_total'], 2, ',', '.')) ?>">
                </div>

                <div id="div_final_antes_desconto" style="display:none" class="form-group col-sm-2">
                    <label for="valor_final_antes_desconto">Valor Antes desconto</label>
                    <input type="text" class="form-control form-control-sm dinheiro" readonly
                        id="valor_final_antes_desconto" name="valor_final_antes_desconto" value="">
                </div>
                <div class="form-group col-sm-2">
                    <label for="valor_final_capeante">Valor Final</label>
                    <input type="text" class="form-control form-control-sm dinheiro" readonly id="valor_final_capeante"
                        name="valor_final_capeante"
                        value="<?php if ($intern['0']['valor_final_capeante'])
                                                                                                                                                                echo (number_format($intern['0']['valor_final_capeante'], 2, ',', '.')) ?>">
                </div>

            </div>
            <hr>
            <!-- campos de informacoes da conta-->
            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label for="pacote">Pacote</label>
                    <select class="form-control form-control-sm" id="pacote" name="pacote">
                        <option value="n" <?= $intern['0']['pacote'] == 'n' ? 'selected' : null ?>>Não</option>
                        <option value="s" <?= $intern['0']['pacote'] == 's' ? 'selected' : null ?>>Sim</option>
                        <option value=""
                            <?= ($intern['0']['pacote'] != 's' and $intern['0']['pacote'] != 'n') ? 'selected' : null ?>>
                            Todos</option>
                    </select>
                </div>

                <div class="form-group col-sm-2">
                    <label for="parcial_capeante">Parcial</label>
                    <select class="form-control form-control-sm" id="parcial_capeante" name="parcial_capeante">
                        <option value="n" <?= $intern['0']['parcial_capeante'] == 'n' ? 'selected' : null ?>>Não
                        </option>
                        <option value="s" <?= $intern['0']['parcial_capeante'] == 's' ? 'selected' : null ?>>Sim
                        </option>
                        <option value=""
                            <?= ($intern['0']['parcial_capeante'] != 's' and $intern['0']['parcial_capeante'] != 'n') ? 'selected' : null ?>>
                            Todos</option>
                    </select>
                </div>

                <div class="form-group col-sm-2">
                    <label for="parcial_num">Parcial No.</label>
                    <input type="number" class="form-control form-control-sm" id="parcial_num" name="parcial_num"
                        value="<?= $intern['0']['parcial_num'] ?>">
                </div>

                <div class="form-group col-sm-2">
                    <label for="senha_finalizada">Senha finalizada</label>
                    <select class="form-control form-control-sm" id="senha_finalizada" name="senha_finalizada">
                        <option value="n" <?= $intern['0']['senha_finalizada'] == 'n' ? 'selected' : null ?>>Não
                        </option>
                        <option value="s" <?= $intern['0']['senha_finalizada'] == 's' ? 'selected' : null ?>>Sim
                        </option>
                        <option value=""
                            <?= ($intern['0']['senha_finalizada'] != 's' and $intern['0']['senha_finalizada'] != 'n') ? 'selected' : null ?>>
                            Todos</option>
                    </select>
                    <p style="font-size:.6em; text-align:center">Selecione se a internação foi finalizada.</p>
                </div>

                <!-- <div class="form-group col-sm-2">
                <label for="negociado_desconto_cap">Desconto</label>
                <select class="form-control" id="negociado_desconto_cap" name="negociado_desconto_cap">
                    <option value="n" <?= $intern['0']['negociado_desconto_cap'] == 'n' ? 'selected' : null ?>>Não
                    </option>
                    <option value="s" <?= $intern['0']['negociado_desconto_cap'] == 's' ? 'selected' : null ?>>Sim
                    </option>
                    <option value=""
                        <?= ($intern['0']['negociado_desconto_cap'] != 's' and $intern['0']['negociado_desconto_cap'] != 'n') ? 'selected' : null ?>>
                        Não</option>
                </select>
                <p style="font-size:.6em; text-align:center">Selecione se houve desconto.</p>
            </div> -->

                <div id="div_val_desconto" class="form-group col-sm-2">
                    <label for="desconto_valor_cap">Valor desconto (em %)</label>
                    <input type="text" class="form-control form-control-sm " id="desconto_valor_cap"
                        name="desconto_valor_cap" value="<?= $intern['0']['desconto_valor_cap'] ?>">
                </div>
                <hr>
                <div class="form-group col-sm-2">
                    <label for="encerrado_cap">Capeante encerrado</label>
                    <select class="form-control form-control-sm" id="encerrado_cap" name="encerrado_cap">
                        <option value="n" <?= $intern['0']['encerrado_cap'] == 'n' ? 'selected' : null ?>>Não</option>
                        <option value="s" <?= $intern['0']['encerrado_cap'] == 's' ? 'selected' : null ?>>Sim</option>
                        <option value=""
                            <?= ($intern['0']['encerrado_cap'] != 's' and $intern['0']['senha_finalizada'] != 'n') ? 'selected' : null ?>>
                            Todos</option>
                    </select>
                    <p style="font-size:.6em; text-align:center">Selecione se o capeante foi encerrado.</p>
                </div>
                <div class="form-group col-sm-2">
                    <label for="conta_parada_cap">Conta parada</label>
                    <select class="form-control form-control-sm" id="conta_parada_cap" name="conta_parada_cap">
                        <option value="n" <?= $intern['0']['conta_parada_cap'] == 'n' ? 'selected' : null ?>>Não
                        </option>
                        <option value="s" <?= $intern['0']['conta_parada_cap'] == 's' ? 'selected' : null ?>>Sim
                        </option>

                    </select>
                    <p style="font-size:.6em; text-align:center">Selecione se a conta esta parada.</p>
                </div>
                <div style="display:none" class="form-group col-sm-2" id="motivo_group">
                    <label for="parada_motivo_cap">Motivo </label>
                    <select class="form-control form-control-sm" id="parada_motivo_cap" name="parada_motivo_cap">
                        <option value="">
                        </option>
                        <option value="<?= $intern['0']['parada_motivo_cap'] ?>"
                            <?= $intern['0']['parada_motivo_cap'] ?>>
                            OPME Pendente
                        </option>
                        <option value="<?= $intern['0']['parada_motivo_cap'] ?>"
                            <?= $intern['0']['parada_motivo_cap'] ?>>
                            Sem
                            autorização
                        </option>
                        <option value="<?= $intern['0']['parada_motivo_cap'] ?>"
                            <?= $intern['0']['parada_motivo_cap'] ?>>
                            Fora prazo
                        </option>
                        <option value="<?= $intern['0']['parada_motivo_cap'] ?>"
                            <?= $intern['0']['parada_motivo_cap'] ?>>
                            Senha cancelada
                        </option>

                    </select>
                </div>
            </div>
            <input type="hidden" class="form-control" value="n" id="aberto_cap" name="aberto_cap">
            <input type="hidden" class="form-control" value="s" id="em_auditoria_cap" name="em_auditoria_cap">

        </div>
        <div> <button style="margin:30px 0 0px 10px" type="submit" class="btn btn-success">Lançar</button>
        </div>
        <br>

    </form>

    <script src="js/DataCapeante.js"></script>
    <script src="js/formatoMoeda.js"></script>
    <script src="js/valoresCapeante.js"></script>
    <script>
    let encerrado_cap = document.getElementById("encerrado_cap");
    let em_auditoria_cap = document.getElementById("em_auditoria_cap");
    let senha_finalizada = document.getElementById("senha_finalizada");
    let conta_parada = document.getElementById("conta_parada_cap");
    const motivoGroup = document.getElementById('motivo_group');


    senha_finalizada.addEventListener("change", function() {

        if (senha_finalizada.value === "s") {

            let encerrado_cap_value = document.getElementById("encerrado_cap").value;
            let em_auditoria_cap_val = document.getElementById("em_auditoria_cap").value;
            em_auditoria_cap_val = "n";

            encerrado_cap.selectedIndex = 1;
            encerrado_cap_value = "s";
            em_auditoria_cap.value = 'n';

        } else {
            let encerrado_cap_value = document.getElementById("encerrado_cap").value;
            let em_auditoria_cap_val = document.getElementById("em_auditoria_cap").value;
            em_auditoria_cap_val = "n";

            encerrado_cap.selectedIndex = 0;
            encerrado_cap_value = "n";
            em_auditoria_cap.value = 's';
        }
    });

    encerrado_cap.addEventListener("change", function() {
        if (encerrado_cap.value === "s") {
            em_auditoria_cap_val = "n";
            em_auditoria_cap.value = 'n';

        } else {
            em_auditoria_cap_val = "n";
            em_auditoria_cap.value = 's';
        }
    });

    conta_parada.addEventListener("change", function() {
        if (conta_parada.value === 's') {
            motivoGroup.style.display = 'block';
        } else {
            motivoGroup.style.display = 'none';
        }

    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>