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
    color: white;
}

.prorrogacao-container #prorrogacoes-json {
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    font-size: 1rem;
}
</style>

<div class="prorrogacao-container" id="container-prorrog" style="display:none;">
    <div id="fieldsContainer">
        <div class="field-container form-group row">
            <input type="hidden" class="form-control" id="fk_internacao_pror" name="fk_internacao_pror"
                value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod1_pror">Acomodação</label>
                <select class="form-control-sm form-control" id="acomod1_pror" name="acomod1_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_ini_pror">Data inicial</label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_ini_pror" name="prorrog1_ini_pror">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_fim_pror">Data final</label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_fim_pror" name="prorrog1_fim_pror">
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="diarias_1">Diárias</label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_1" name="diarias_1">
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="isol_1_pror">Isolamento</label>
                <select class="form-control-sm form-control" id="isol_1_pror" name="isol_1_pror">
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
    </div>
    <button type="button" class="btn btn-add" onclick="generateJSON()">Gerar JSON</button>
    <input type="hidden" id="prorrogacoes-json" name="prorrogacoes-json">
    <textarea id="json-preview" rows="10" readonly placeholder="Pré-visualização do JSON"></textarea>
</div>

<script>
// Exibe o container ao carregar a página
const dataInternacao = "2023-10-15"; // Exemplo de data de internação, altere conforme necessário.
document.addEventListener("DOMContentLoaded", function() {
    const selectProrrog = document.getElementById("select_prorrog");
    if (selectProrrog && selectProrrog.value === "s") {
        document.getElementById("container-prorrog").style.display = "block";
    }
});

// Função para criar novos campos dinâmicos
function createProrrogationField() {
    return `
        <div class="field-container form-group row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod1_pror">Acomodação</label>
                <select class="form-control-sm form-control" id="acomod1_pror" name="acomod1_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_ini_pror">Data inicial</label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_ini_pror" name="prorrog1_ini_pror">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_fim_pror">Data final</label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_fim_pror" name="prorrog1_fim_pror">
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="diarias_1">Diárias</label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_1" name="diarias_1">
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="isol_1_pror">Isolamento</label>
                <select class="form-control-sm form-control" id="isol_1_pror" name="isol_1_pror">
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

// Função para adicionar campos dinâmicos
function addField() {
    const fieldsContainer = document.getElementById("fieldsContainer");
    const newField = createProrrogationField();
    fieldsContainer.insertAdjacentHTML("beforeend", newField);
}

// Função para remover campos dinâmicos
function removeField(button) {
    const fieldContainer = button.closest(".field-container");
    fieldContainer.remove();
}

// Função para calcular as diárias e validar as datas
function calculateDiarias(container) {
    const dataAtual = new Date().toISOString().split("T")[0];
    const dataInicial = container.querySelector('[name="prorrog1_ini_pror"]').value;
    const dataFinal = container.querySelector('[name="prorrog1_fim_pror"]').value;
    const diariasField = container.querySelector('[name="diarias_1"]');
    const errorMessage = container.querySelector(".error-message");

    errorMessage.textContent = ""; // Limpa mensagens de erro

    if (dataInicial && dataFinal) {
        const inicio = new Date(dataInicial);
        const fim = new Date(dataFinal);
        const internacao = new Date(dataInternacao);

        if (fim < inicio) {
            errorMessage.textContent = "A data final não pode ser menor que a data inicial.";
            errorMessage.style.display = "block";
            diariasField.value = "";
            return;
        }

        if (fim < internacao || fim > new Date(dataAtual)) {
            errorMessage.textContent =
                "A data final não pode ser menor que a data de internação ou maior que a data atual.";
            errorMessage.style.display = "block";
            diariasField.value = "";
            return;
        }

        const diffTime = Math.abs(fim - inicio);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        diariasField.value = diffDays;
        errorMessage.style.display = "none";
    }
}

// Adiciona listeners para validação automática ao alterar as datas
document.getElementById("fieldsContainer").addEventListener("input", (event) => {
    const fieldContainer = event.target.closest(".field-container");
    if (fieldContainer) {
        calculateDiarias(fieldContainer);
    }
});

// Função para gerar o JSON
function generateJSON() {
    const fkInternacaoPror = document.getElementById("fk_internacao_pror").value;
    const fieldContainers = document.querySelectorAll(".field-container");
    const prorrogations = Array.from(fieldContainers).map((container) => {
        return {
            fkInternacaoPror: fkInternacaoPror,
            acomodacao: container.querySelector('[name="acomod1_pror"]').value,
            dataInicial: container.querySelector('[name="prorrog1_ini_pror"]').value,
            dataFinal: container.querySelector('[name="prorrog1_fim_pror"]').value,
            isolamento: container.querySelector('[name="isol_1_pror"]').value,
            diarias: container.querySelector('[name="diarias_1"]').value,
        };
    });

    const jsonData = {
        prorrogations,
    };

    const jsonString = JSON.stringify(jsonData, null, 2);
    document.getElementById("prorrogacoes-json").value = jsonString;
    document.getElementById("json-preview").value = jsonString;
}
</script>