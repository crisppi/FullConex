<?php
include_once("array_dados.php");
$id_internacao = filter_input(INPUT_GET, 'id_internacao') ?: null;

$a = ($findMaxGesInt[0]);
$ultimoReg = ($a["ultimoReg"]);
$ultimoReg = $ultimoReg + 1;

$condicoes = [
    strlen($id_internacao) ? 'id_internacao = "' . $id_internacao . '"' : NULL,

];
$condicoes = array_filter($condicoes);
// REMOVE POSICOES VAZIAS DO FILTRO
$where = implode(' AND ', $condicoes);
$order = $obLimite = null;
// PREENCHIMENTO DO FORMULARIO COM QUERY
$query = $internacaoDao->selectAllInternacao($where, $order, $obLimite);

extract($query);
?>
<div style="margin:15px" class=" row" id='main-container'>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <div class="form-group row">
        <h4 class="page-title">Editar internação</h4>
        <hr>
    </div>
    <form class="visible" action="<?= $BASE_URL ?>process_censo.php" id="myForm" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="typeForm" value="update">
        <input type="hidden" class="form-control" value="n" id="censo_int" name="censo_int">
        <input type="hidden" name="id_internacaoCenso" id="id_internacaoCenso"
            value="<?= $query['0']['id_internacao']; ?>">
        <div class="form-group row">
            <div class="form-group col-sm-3">
                <label class="control-label col-sm-3 " for="fk_hospital_int">Hospital</label>
                <select class="form-control" id="fk_hospital_int" name="fk_hospital_int" required>
                    <option value="<?= $query['0']["fk_hospital_int"] ?>"
                        <?php if ($query['0']['nome_hosp'] == $hospital['0']['nome_hosp']) echo 'selected'; ?>>
                        <?php echo $query['0']['nome_hosp']; ?></option>
                    <option value="<?= $query['0']["fk_paciente_int"] ?>">Selecione o Hospital</option>
                    <?php foreach ($listHopitaisPerfil as $hospital) : ?>
                    <option value="<?= $hospital["id_hospital"] ?>"><?= $hospital["nome_hosp"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-3">
                <label class="control-label" for="fk_paciente_int">Paciente</label>
                <select class="form-control" id="fk_paciente_int" name="fk_paciente_int" required>
                    <option value="<?= $query['0']["id_paciente"] ?>"
                        <?php if ($query['0']['nome_pac'] == $paciente['0']["nome_pac"]) echo 'selected'; ?>>
                        <?php echo $query['0']['nome_pac']; ?></option> <?php foreach ($pacientes as $paciente) : ?>
                    <option value="<?= $paciente["id_paciente"] ?>"><?= $paciente["nome_pac"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php $dataAtual = date('Y-m-d');
            ?>

            <div class="form-group col-sm-2">
                <label class="control-label" for="data_intern_int">Data Internação</label>
                <input type="date" class="form-control" value="<?php echo $query['0']['data_intern_int'] ?>"
                    id="data_intern_int" name="data_intern_int">
                <div class="notif-input oculto" id="notif-input">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-2">
                <label for="data_visita_int">Data Visita</label>
                <input type="date" value='<?= $dataAtual; ?>' class="form-control" id="data_visita_int"
                    name="data_visita_int" readonly>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="internado_int">Internado</label>
                <select class="form-control" id="internado_int" name="internado_int">
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <!-- ENTRADA DE DADOS AUTOMATICOS NO INPUT-->
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="s" id="primeira_vis_int" name="primeira_vis_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" value="0" id="visita_no_int" name="visita_no_int">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_enf_int" name="visita_enf_int"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                        echo 's';
                                                                                                                    } else {
                                                                                                                        echo 'n';
                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                        's';
                                                                                                                                    } else {
                                                                                                                                        'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_med_int" name="visita_med_int"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                                        echo 's';
                                                                                                                    } else {
                                                                                                                        echo 'n';
                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                                                        's';
                                                                                                                                    } else {
                                                                                                                                        'n';
                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_enf" name="visita_auditor_prof_enf"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                        echo ($_SESSION['login_user']);
                                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) === 'Enf_auditor') {
                                                                                                                                                        ($_SESSION['login_user']);
                                                                                                                                                    }; ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" id="visita_auditor_prof_med" name="visita_auditor_prof_med"
                    placeholder="<?php if (($_SESSION['cargo']) === 'Med_auditor') {
                                                                                                                                        echo ($_SESSION['login_user']);
                                                                                                                                    }; ?>"
                    value="<?php if (($_SESSION['cargo']) == 'Med_auditor') {
                                                                                                                                                        ($_SESSION['login_user']);
                                                                                                                                                    }; ?>">
            </div>

        </div>
        <div class="row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomodacao_int">Acomodação</label>
                <select class="form-control" id="acomodacao_int" name="acomodacao_int">
                    <option <?php if ($query['0']['acomodacao_int'] == $dados_acomodacao["0"]) echo 'selected'; ?>>
                        <?php echo $query['0']['acomodacao_int']; ?></option>

                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="especialidade_int">Especialidade</label>
                <select class="form-control" id="especialidade_int" name="especialidade_int">
                    <option value="">Selecione</option>
                    <?php
                    sort($dados_especialidade, SORT_ASC);
                    foreach ($dados_especialidade as $especial) { ?>
                    <option value="<?= $especial; ?>"><?= $especial; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-3">
                <label for="titular_int">Médico</label>
                <input type="text" class="form-control" id="titular_int" name="titular_int"
                    value="<?= $query['0']['titular_int']; ?>">
            </div>
            <div class="form-group col-sm-1">
                <label for="crm_int">CRM</label>
                <input type="text" class="form-control" id="crm_int" value="<?= $query['0']['crm_int']; ?>"
                    name="crm_int">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="modo_internacao_int">Modo Internação</label>
                <select class="form-control" id="modo_internacao_int" name="modo_internacao_int">
                    <option <?php if ($query['0']['modo_internacao_int'] == $modo_internacao) echo 'selected'; ?>>
                        <?php echo $query['0']['modo_internacao_int']; ?></option>
                    <?php sort($modo_internacao, SORT_ASC);
                    foreach ($modo_internacao as $modo) { ?>
                    <option value="<?= $modo; ?>"><?= $modo; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tipo_admissao_int">Tipo Internação</label>
                <select class="form-control" id="tipo_admissao_int" name="tipo_admissao_int">
                    <option <?php if ($query['0']['tipo_admissao_int'] == $tipo_admissao["0"]) echo 'selected'; ?>>
                        <?php echo $query['0']['tipo_admissao_int']; ?></option>
                    <?php sort($tipo_admissao, SORT_ASC);
                    foreach ($tipo_admissao as $tipo) { ?>
                    <option value="<?= $tipo; ?>"><?= $tipo; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia_int">Patologia</label>
                <select class="form-control" id="fk_patologia_int" name="fk_patologia_int">
                    <option value="">Patologia</option>
                    <?php foreach ($patologias as $patologia) : ?>
                    <option value="<?= $patologia["id_patologia"] ?>"><?= $patologia["patologia_pat"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="fk_patologia2">Antecedente</label>
                <select class="form-control" id="fk_patologia2" name="fk_patologia2">
                    <option value="">Patologia</option>
                    <?php foreach ($patologias as $patologia) : ?>
                    <option value="<?= $patologia["id_patologia"] ?>"><?= $patologia["patologia_pat"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="grupo_patologia_int">Grupo Patologia</label>
                <select class="form-control" id="grupo_patologia_int" name="grupo_patologia_int">
                    <option value="">Grupo</option>
                    <?php foreach ($dados_grupo_pat as $grupo) : ?>
                    <option value="<?= $grupo ?>"><?= $grupo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label for="senha_int">Senha</label>
                <input type="text" class="form-control" id="senha_int" value="<?= $query['0']['senha_int']; ?>"
                    name="senha_int">
            </div>
            <div class="form-group col-sm-3">
                <label for="usuario_create_int">Usuário</label>
                <input type="text" class="form-control" id="usuario_create_int" value="<?= $_SESSION['email_user'] ?>"
                    name="usuario_create_int" readonly>
            </div>
            <div class="form-group row">
                <div>
                    <label for="rel_int">Relatório Auditoria</label>
                    <textarea type="textarea" rows="2" onclick="aumentarTextAudit()" class="form-control" id="rel_int"
                        name="rel_int"></textarea>
                </div>
                <div>
                    <label for="acoes_int">Ações Auditoria</label>
                    <textarea rows="2" onclick="aumentarTextAcoes()" type="textarea" class="form-control" id="acoes_int"
                        name="acoes_int"></textarea>
                </div>
                <div class="form-group col-sm-3">
                    <?php $agora = date('Y-m-d'); ?>
                    <input type="hidden" class="form-control" id="data_create_int" value='<?= $agora; ?>'
                        name="data_create_int">
                </div>
                <div>
                    <hr>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_prorrog">Prorrogação</label>
                    <select class="form-control" id="select_prorrog" name="select_prorrog">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_gestao">Gestão</label>

                    <select class="form-control" id="select_gestao" name="select_gestao">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_uti">UTI</label>
                    <select class="form-control" id="select_uti" name="select_uti">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="select_negoc">Negociações</label>
                    <select class="form-control" id="select_negoc" name="select_negoc">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
            </div>
            <div>
                <hr>
            </div>
            <input type="hidden" class="form-control" value="<?= $ultimoReg ?>" id="fk_int_capeante"
                name="fk_int_capeante">
            <input type="hidden" class="form-control" value="n" id="encerrado_cap" name="encerrado_cap">
            <input type="hidden" class="form-control" value="s" id="aberto_cap" name="aberto_cap">
            <input type="hidden" class="form-control" value="n" id="em_auditoria_cap" name="em_auditoria_cap">
            <input type="hidden" class="form-control" value="n" id="senha_finalizada" name="senha_finalizada">

            <!-- FORMULARIO DE GESTÃO -->
            <?php include_once('formularios/form_cad_internacao_censo_gestao.php'); ?>

            <!-- FORMULARIO DE UTI -->
            <?php include_once('formularios/form_cad_internacao_censo_uti.php'); ?>

            <!-- FORMULARIO DE PRORROGACOES -->
            <?php include_once('formularios/form_cad_internacao_censo_prorrog.php'); ?>

            <!-- <FORMULARO DE NEGOCIACOES -->
            <?php include_once('formularios/form_cad_internacao_censo_negoc.php'); ?>

            <br>
            <div> <button style="margin-left:10px" type="submit" class="btn-lg btn-success">Cadastrar</button>
            </div>
            <div style="margin-left:20px; width:500px" class="alert" id="alert" role="alert"></div>

            <br>
    </form>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="js/scriptDataInt.js"></script>

<script type="text/javascript">
// aparecer e pegar dados do select do Gestao
$('#select_gestao').change(function() {
    var option = $('#select_gestao').find(":selected").text();

    console.log(option);
    if (option == "Sim") {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");


        if (divGes.style.display === "none") {
            divGes.style.display = "block";
            divPro.style.display = "none";
            divUti.style.display = "none";
            divNeg.style.display = "none";

        } else {
            divGes.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");
        divGes.style.display = "none";

    };
});

// aparecer e pegar dados do select UTI

$('#select_uti').change(function() {
    var option = $('#select_uti').find(":selected").text();

    console.log(option);
    if (option == "Sim") {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");


        if (divUti.style.display === "none") {
            divUti.style.display = "block";
            divPro.style.display = "none";
            divGes.style.display = "none";
            divNeg.style.display = "none";

        } else {
            divUti.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divUti.style.display = "none";

    };
});

// aparecer e pegar dados do select prorrogacao

$('#select_prorrog').change(function() {
    var option = $('#select_prorrog').find(":selected").val();

    console.log(option);
    if (option == "Sim") {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        if (divPro.style.display === "none") {
            divPro.style.display = "block";
            divGes.style.display = "none";
            divUti.style.display = "none";
            divNeg.style.display = "none";


        } else {
            divPro.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divPro.style.display = "none";

    };
});

// aparecer e pegar dados do select negociacao

$('#select_negoc').change(function() {
    var option = $('#select_negoc').find(":selected").text();

    console.log(option);
    if (option == "Sim") {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        if (divNeg.style.display === "none") {
            divNeg.style.display = "block";
            divPro.style.display = "none";
            divUti.style.display = "none";
            divGes.style.display = "none";

        } else {
            divNeg.style.display = "none";
        }

    } else {
        var divGes = document.querySelector("#container-gestao");
        var divPro = document.querySelector("#container-prorrog");
        var divUti = document.querySelector("#container-uti");
        var divNeg = document.querySelector("#container-negoc");

        divNeg.style.display = "none";

    };
});





//*** ADICIONAR PRORROGACAO */
function mostrarGrupo2(el) {
    var display = document.getElementById(el).style.display;
    if (display == "none")
        document.getElementById(el).style.display = 'flex';
    else
        document.getElementById(el).style.display = 'none';
}

function mostrarGrupo3(el) {
    var display = document.getElementById(el).style.display;
    if (display == "none")
        document.getElementById(el).style.display = 'block';
    else
        document.getElementById(el).style.display = 'none';
}
</script>


<script>
// mudar linhas do relatorio 
var text_audit = document.querySelector("#rel_int");

function aumentarTextAudit() {
    if (text_audit.rows == "2") {
        text_audit.rows = "20"
    } else {
        text_audit.rows = "2"
    }
}

// mudar linhas da acoes 
var text_acoes = document.querySelector("#acoes_int");

function aumentarTextAcoes() {
    if (text_acoes.rows == "2") {
        text_acoes.rows = "20"
    } else {
        text_acoes.rows = "2"
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>