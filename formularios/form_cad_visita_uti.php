<div id="container-uti" style="display:none; margin:5px">
    <hr>
    <h6 class="page-title">Adicione informações sobre a internação na UTI</h6>
    <input type="hidden" name="type" value="create">

    <div class="form-group row">
        <?php
        $a = ($prorrogacaoIdMax[0]); // pegar dado da ultima visita
        $ultimoReg = ($a["ultimoReg"]);
        ?>

        <div class="form-group row">
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" readonly id="fk_internacao_uti" name="fk_internacao_uti"
                    value="<?= $id_internacao ?>">
            </div>
            <div class="form-group col-sm-1">
                <input type="hidden" class="form-control" readonly id="fk_visita_uti" name="fk_visita_uti"
                    value="<?= $visitaMax['0']['id_visita']; ?>">
            </div>
        </div>
    </div>
    <div class="form-group row">

        <div class="form-group col-sm-2">
            <label for="internado_uti">Internado UTI</label>
            <select class="form-control-sm form-control" id="internado_uti" name="internado_uti">
                <option value="s">Sim</option>
                <option value="n">Não</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="motivo_uti">Motivo</label>
            <select class="form-control-sm form-control" id="motivo_uti" name="motivo_uti">
                <option value=" ">Selecione</option>
                <?php
                sort($dados_UTI, SORT_ASC);
                foreach ($dados_UTI as $uti) { ?>
                <option value="<?= $uti; ?>">
                    <?= $uti; ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="just_uti">Justificativa</label>
            <select class="form-control-sm form-control" id="just_uti" name="just_uti">
                <option value="Pertinente">Pertinente</option>
                <option value="Não pertinente">Não pertinente</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="criterio_uti">Critério</label>
            <select class="form-control-sm form-control" id="criterio_uti" name="criterio_uti">
                <option value=" ">Selecione</option>
                <?php
                sort($criterios_UTI, SORT_ASC);
                foreach ($criterios_UTI as $uti) { ?>
                <option value="<?= $uti; ?>">
                    <?= $uti; ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="data_internacao_uti">Data internação UTI</label>
            <input type="date" class="form-control-sm form-control" id="data_internacao_uti"
                value="<?php echo date('Y-m-d') ?>" name="data_internacao_uti">
        </div>
        <div class="form-group col-sm-1">
            <label for="hora_internacao_uti">Hora</label>
            <input type="time" class="form-control-sm form-control" id="hora_internacao_uti"
                value="<?php echo date('H:i') ?>" name="hora_internacao_uti">
        </div>
    </div>
    <input type="hidden" class="form-control-sm form-control" id="fk_user_uti" value="<?= $_SESSION['id_usuario'] ?>"
        name="fk_user_uti">

    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label for="vm_uti">Ventilação Mecânica</label>
            <select class="form-control-sm form-control" id="vm_uti" name="vm_uti">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="dva_uti">DVA</label>
            <select class="form-control-sm form-control" id="dva_uti" name="dva_uti">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="suporte_vent_uti">Suporte Ventilatório Não invasivo </label>
            <select class="form-control-sm form-control" id="suporte_vent_uti" name="suporte_vent_uti">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="glasgow_uti">Escala de Glasgow</label>
            <select class="form-control-sm form-control" id="glasgow_uti" name="glasgow_uti">
                <option value="">Sel</option>
                <option value="3-4">3-4</option>
                <option value="5-8">5-8</option>
                <option value="8-10">8-10</option>
                <option value="10-12">10-12</option>
                <option value="12-15">12-15</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="dist_met_uti">Distúrbio Metabólico</label>
            <select class="form-control-sm form-control" id="dist_met_uti" name="dist_met_uti">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
    </div>
    <div class="form-group row">

        <div class="form-group col-sm-2">
            <label for="score_uti">Score</label>
            <select class="form-control-sm form-control" id="score_uti" name="score_uti">
                <option value="">Selecione</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>
        <div class="form-group col-sm-10" id="justifique_uti" style="display: none;">
            <label for="justifique_uti">Justifique permanência - Critério baixo</label>
            <p id="criteria_message" style="display: inline; margin-left: 10px; font-size: 0.9em; color: #555;"></p>
            <textarea type="textarea" style="resize:none" rows="2" class="form-control" id="justifique_uti"
                name="justifique_uti"></textarea>
        </div>

        <div class="form-group col-sm-2">
            <label for="saps_uti">Saps</label>
            <select class="form-control-sm form-control" id="saps_uti" name="saps_uti">
                <option value=" ">Selecione</option>
                <?php
                sort($dados_saps, SORT_ASC);
                foreach ($dados_saps as $saps) { ?>
                <option value="<?= $saps; ?>">
                    <?= $saps; ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <div style="margin-top:30px " class="form-group col-sm-2">
            <a style="color:blue; font-size:0.8em" href="https://www.rccc.eu/ppc/indicadores/saps3.html"
                target="_blank">Calcular SAPS</a>
        </div>
    </div>
    <div>
        <label for="rel_uti">Relatório UTI</label>
        <textarea type="textarea" style="resize:none" onclick="aumentarTextUTI()" rows="2" class="form-control"
            id="rel_uti" name="rel_uti"></textarea>
    </div>
    <!-- Scripts no final do body -->
    <script>
    // mudar linhas do text relatorio UTI 
    var text_relatorio_uti = document.querySelector("#rel_uti");

    function aumentarTextUTI() {
        if (text_relatorio_uti.rows == "2") {
            text_relatorio_uti.rows = "30"
        } else {
            text_relatorio_uti.rows = "2"
        }
    }
    </script>

    <script>
    // Selecionar os elementos necessários
    const dva = document.querySelector("#dva_uti");
    const vm = document.querySelector("#vm_uti");
    const glasgow = document.querySelector("#glasgow_uti");
    const dist = document.querySelector("#dist_met_uti");
    const suporteVent = document.querySelector("#suporte_vent_uti");
    const criterioSelect = document.querySelector("#criterio_uti");
    var scoreSelect = document.querySelector("#score_uti");
    var justifyDiv = document.querySelector("#justifique_uti");
    var criteriaMessage = document.querySelector("#criteria_message");

    document.addEventListener("DOMContentLoaded", function() {
        console.log("Página carregada, verificando elementos...");
        console.log("DVA:", dva);
        console.log("VM:", vm);
        console.log("Glasgow:", glasgow);
        console.log("Distúrbio Metabólico:", dist);
        console.log("Suporte Ventilatório:", suporteVent);
    });

    function avaliarStatus() {
        let status = "";
        let score = "";
        let color = "";

        console.log("Valores atuais:");
        console.log("DVA:", dva?.value);
        console.log("VM:", vm?.value);
        console.log("Glasgow:", glasgow?.value);
        console.log("Distúrbio Metabólico:", dist?.value);
        console.log("Suporte Ventilatório:", suporteVent?.value);

        if (dva?.value === "s" || vm?.value === "s" ||
            (glasgow?.value === "3-4" || glasgow?.value === "5-8" || glasgow?.value === "8-10") || dist?.value === "s"
            ) {
            status = "UTI";
            criterioSelect.value = "1";
            score = "1";
            justifyDiv.style.display = "none";
            criteriaMessage.textContent = "";
            criteriaMessage.style.color = "";
            color = "green";
        } else if (dva?.value === "n" && vm?.value === "n" &&
            (glasgow?.value === "10-12" || glasgow?.value === "12-15") && suporteVent?.value === "s") {
            status = "Semi";
            criterioSelect.value = "2";
            score = "2";
            justifyDiv.style.display = "block";
            criteriaMessage.textContent = "Paciente com critérios para Semi";
            criteriaMessage.style.color = "orange";
            color = "orange";
        } else {
            status = "Apto";
            criterioSelect.value = "3";
            score = "3";
            justifyDiv.style.display = "block";
            criteriaMessage.textContent = "Paciente com critérios para Apto";
            criteriaMessage.style.color = "red";
            color = "red";
        }

        if (scoreSelect) {
            scoreSelect.value = score;
            scoreSelect.style.borderColor = color;
            scoreSelect.style.color = color;
        } else {
            console.error("scoreSelect não encontrado.");
        }
    }

    [dva, vm, glasgow, dist, suporteVent].forEach((element) => {
        if (element) {
            element.addEventListener("change", () => {
                console.log(`Campo ${element.id} alterado para ${element.value}`);
                avaliarStatus();
            });
        } else {
            console.error("Elemento não encontrado ou inválido:", element);
        }
    });

    document.addEventListener("DOMContentLoaded", avaliarStatus);
    </script>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>