<style>
.prorrogacao-container .form-group.row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-start;
}

.prorrogacao-container .form-group {
    margin-bottom: 15px;
}

.prorrogacao-container .form-group label {
    margin-bottom: 5px;
    font-weight: bold;
}

.prorrogacao-container .form-control {
    width: 100%;
    padding: 5px;
}

.prorrogacao-container .btn {
    padding: 5px 10px;
    font-size: 0.9rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.prorrogacao-container .btn-add {
    background-color: #007bff;
    color: white;
}

.prorrogacao-container .btn-remove {
    background-color: #dc3545;
    color: #fff;
}

.prorrogacao-container #prorrogacoes-json {
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    font-size: 1rem;
}
.adicional-card {
    background:#fff;
    border-radius:22px;
    border:1px solid #ebe1f5;
    box-shadow:0 12px 28px rgba(45,18,70,.08);
    padding:22px 24px;
}
.adicional-card__header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    margin-bottom:18px;
}
.adicional-card__title {
    display:flex;
    align-items:center;
    margin:0;
    color:#3a184f;
    font-weight:600;
}
.adicional-card__marker {
    width:6px;
    height:26px;
    border-radius:10px;
    margin-right:12px;
    background:linear-gradient(180deg,#8f5ff3,#b995ff);
}
</style>

<div class="prorrogacao-container" id="container-prorrog" style="display:none;">
    <div class="adicional-card">
        <div class="adicional-card__header">
            <h4 class="adicional-card__title">
                <span class="adicional-card__marker"></span>
                Prorrogação
            </h4>

            <?php if (!empty($prorrogIntern) && count($prorrogIntern) > 0): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProrrog"
                id="openmodal">
                <i class="fas fa-eye"></i> Prorrogações Anteriores
            </button>
            <?php endif; ?>
        </div>
    <div id="fieldsContainer">

        <!-- Linha inicial (sem botão "-") -->
        <div class="field-container form-group row">
            <input type="hidden" id="fk_internacao_pror" name="fk_internacao_pror" value="<?= $ultimoReg ?>">
            <input type="hidden" id="fk_usuario_pror" name="fk_usuario_pror" value="<?= $_SESSION["id_usuario"] ?>">

            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod1_pror">Acomodação</label>
                <select onchange="generateProrJSON()" class="form-control-sm form-control" id="acomod1_pror"
                    name="acomod1_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_ini_pror">Data inicial</label>
                <input onchange="generateProrJSON()" type="date" class="form-control-sm form-control"
                    id="prorrog1_ini_pror" name="prorrog1_ini_pror">
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_fim_pror">Data final</label>
                <input onchange="generateProrJSON()" type="date" class="form-control-sm form-control"
                    id="prorrog1_fim_pror" name="prorrog1_fim_pror">
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="diarias_1">Diárias</label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_1" name="diarias_1">
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label" for="isol_1_pror">Isolamento</label>
                <select onchange="generateProrJSON()" class="form-control-sm form-control" id="isol_1_pror"
                    name="isol_1_pror">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>

            <div class="form-group col-sm-2" style="margin-top:25px">
                <!-- apenas (+) na primeira linha -->
                <button type="button" class="btn btn-add" onclick="addField()">+</button>
            </div>

            <p class="error-message" style="color:red; font-size:0.8em; display:none;"></p>
        </div>
    </div>

    <input type="hidden" id="prorrogacoes-json" name="prorrogacoes-json">
    </div>
</div>

<div class="modal fade" id="modalProrrog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background:#5e2363;">
                <h4 class="page-title" style="color:white">Prorrogações</h4>
                <p class="page-description" style="color:white; margin-top:5px">Informações sobre prorrogações
                    anteriores</p>
            </div>
            <div class="modal-body">
                <?php
                if (empty($visitas)) {
                    echo ("<br>");
                    echo ("<p style='margin-left:100px'> <b>-- Esta internação ainda não possui Prorrogações  -- </b></p>");
                    echo ("<br>");
                } else { ?>
                <table class="table table-sm table-striped table-hover table-condensed">
                    <thead>
                        <tr>
                            <th scope="col" style="width:5%">Id</th>
                            <th scope="col" style="width:10%">Acomodação</th>
                            <th scope="col" style="width:15%">Início</th>
                            <th scope="col" style="width:15%">Fim</th>
                            <th scope="col" style="width:15%">Diárias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prorrogIntern as $intern) {
                            $idProrrog = $intern["id_prorrogacao"] ?? "Desconhecido";
                            $acomod = $intern["acomod1_pror"] ?? "Desconhecido";
                            $inicio = !empty($intern['prorrog1_ini_pror']) ? date("d/m/Y", strtotime($intern['prorrog1_ini_pror'])) : "--";
                            $fim    = !empty($intern['prorrog1_fim_pror']) ? date("d/m/Y", strtotime($intern['prorrog1_fim_pror'])) : "--";
                            $diarias = $intern["diarias_1"] ?? "--";
                        ?>
                        <tr>
                            <td><?= $idProrrog ?></td>
                            <td><?= $acomod ?></td>
                            <td><?= $inicio ?></td>
                            <td><?= $fim ?></td>
                            <td><?= $diarias ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>
</div>

<script>
// Exibe o container ao carregar a página, se "select_prorrog" estiver marcado
document.addEventListener("DOMContentLoaded", function() {
    const selectProrrog = document.getElementById("select_prorrog");
    if (selectProrrog && selectProrrog.value === "s") {
        document.getElementById("container-prorrog").style.display = "block";
    }
});

// Preenche a data inicial da primeira linha com a data da internação
function setFirstProrrogationDate() {
    const dataInternacaoEl = document.getElementById("data_intern_int");
    if (!dataInternacaoEl) return;
    const dataInternacao = dataInternacaoEl.value;
    const firstContainer = document.querySelector(".field-container");
    if (firstContainer && dataInternacao) {
        firstContainer.querySelector('[name="prorrog1_ini_pror"]').value = dataInternacao;
    }
}
const dataInternInput = document.getElementById("data_intern_int");
if (dataInternInput) dataInternInput.addEventListener("change", setFirstProrrogationDate);
document.addEventListener("DOMContentLoaded", setFirstProrrogationDate);

// Template de novas linhas (com "-" e "+")
function createProrrogationField() {
    return `
        <div class="field-container form-group row">
            <input type="hidden" name="fk_internacao_pror" value="<?= $ultimoReg ?>">
            <input type="hidden" name="fk_usuario_pror" value="<?= $_SESSION["id_usuario"] ?>">

            <div class="form-group col-sm-2">
                <label class="control-label">Acomodação</label>
                <select onchange="generateProrJSON()" class="form-control-sm form-control" name="acomod1_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                        <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label">Data inicial</label>
                <input onchange="generateProrJSON()" type="date" class="form-control-sm form-control" name="prorrog1_ini_pror">
            </div>

            <div class="form-group col-sm-2">
                <label class="control-label">Data final</label>
                <input onchange="generateProrJSON()" type="date" class="form-control-sm form-control" name="prorrog1_fim_pror">
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label">Diárias</label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly class="form-control-sm form-control" name="diarias_1">
            </div>

            <div class="form-group col-sm-1">
                <label class="control-label">Isolamento</label>
                <select onchange="generateProrJSON()" class="form-control-sm form-control" name="isol_1_pror">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>

            <div class="form-group col-sm-2" style="margin-top:25px">
                <button type="button" class="btn btn-remove" onclick="removeField(this)">-</button>
                <button type="button" class="btn btn-add" onclick="addField()">+</button>
            </div>

            <p class="error-message" style="color:red; font-size:0.8em; display:none;"></p>
        </div>
    `;
}

// Adiciona nova linha e encadeia datas
function addField() {
    const fieldsContainer = document.getElementById("fieldsContainer");
    const newField = createProrrogationField();
    fieldsContainer.insertAdjacentHTML("beforeend", newField);

    const fieldContainers = document.querySelectorAll(".field-container");
    if (fieldContainers.length > 1) {
        const lastContainer = fieldContainers[fieldContainers.length - 2];
        const newContainer = fieldContainers[fieldContainers.length - 1];
        const lastEndDate = lastContainer.querySelector('[name="prorrog1_fim_pror"]').value;
        if (lastEndDate) newContainer.querySelector('[name="prorrog1_ini_pror"]').value = lastEndDate;
    }
    generateProrJSON();
}

// Remove linha (apenas nas linhas novas)
function removeField(button) {
    const fieldContainer = button.closest(".field-container");
    if (!fieldContainer) return;
    fieldContainer.remove();
    generateProrJSON();
}

// Calcula diárias e valida datas
function calculateDiarias(container) {
    const dataAtual = new Date().toISOString().split("T")[0];
    const dataInternacaoEl = document.getElementById("data_intern_int");
    const dataInternacao = dataInternacaoEl ? dataInternacaoEl.value : null;

    const dataInicial = container.querySelector('[name="prorrog1_ini_pror"]').value;
    const dataFinal = container.querySelector('[name="prorrog1_fim_pror"]').value;
    const diariasField = container.querySelector('[name="diarias_1"]');
    const errorMessage = container.querySelector(".error-message");

    errorMessage.textContent = ""; // limpa

    if (dataInicial && dataFinal) {
        const inicio = new Date(dataInicial);
        const fim = new Date(dataFinal);
        const internacao = dataInternacao ? new Date(dataInternacao) : null;

        if (internacao && inicio < internacao) {
            errorMessage.textContent = "A data inicial não pode ser menor que a data de internação.";
            errorMessage.style.display = "block";
            diariasField.value = "";
            return;
        }
        if (fim < inicio) {
            errorMessage.textContent = "A data final não pode ser menor que a data inicial.";
            errorMessage.style.display = "block";
            diariasField.value = "";
            return;
        }
        if (fim > new Date(dataAtual)) {
            errorMessage.textContent = "A data final não pode ser maior que a data atual.";
            errorMessage.style.display = "block";
            diariasField.value = "";
            return;
        }

        const diffTime = Math.abs(fim - inicio);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        diariasField.value = diffDays;
        errorMessage.style.display = "none";
        generateProrJSON();
    }
}

// Validação automática ao alterar datas/seleções (delegado)
document.getElementById("fieldsContainer").addEventListener("input", (event) => {
    const fieldContainer = event.target.closest(".field-container");
    if (fieldContainer) calculateDiarias(fieldContainer);
});

// Gera JSON das prorrogações
function generateProrJSON() {
    // pega fk_internacao e usuario da primeira linha (evita conflito de IDs repetidos)
    const first = document.querySelector(".field-container");
    const fk_internacao_pror = first ? (first.querySelector('input[name="fk_internacao_pror"]')?.value || '') : '';
    const fk_usuario_pror = first ? (first.querySelector('input[name="fk_usuario_pror"]')?.value || '') : '';

    const fieldContainers = document.querySelectorAll(".field-container");
    const prorrogations = Array.from(fieldContainers).map((container) => ({
        fk_internacao_pror: fk_internacao_pror,
        fk_usuario_pror: fk_usuario_pror,
        acomod1_pror: container.querySelector('[name="acomod1_pror"]').value,
        prorrog1_ini_pror: container.querySelector('[name="prorrog1_ini_pror"]').value,
        prorrog1_fim_pror: container.querySelector('[name="prorrog1_fim_pror"]').value,
        isol_1_pror: container.querySelector('[name="isol_1_pror"]').value,
        diarias_1: container.querySelector('[name="diarias_1"]').value,
    }));

    const jsonData = {
        prorrogations
    };
    document.getElementById("prorrogacoes-json").value = JSON.stringify(jsonData, null, 2);
}

// Limpa todos os inputs (mantém só a primeira linha)
function clearProrrogInputs() {
    const fieldContainers = document.querySelectorAll(".field-container");
    fieldContainers.forEach((container, index) => {
        if (index > 0) {
            container.remove();
        } else {
            container.querySelectorAll('input:not([type="hidden"])').forEach((input) => {
                input.value = '';
            });
            container.querySelectorAll('select').forEach((select) => {
                select.selectedIndex = 0;
            });
        }
    });
    generateProrJSON();
}
</script>
