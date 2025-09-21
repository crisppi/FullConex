<!-- <link rel="stylesheet" href="css/dialog.css"> Link para o arquivo CSS externo -->
<style>
/* Estilo para a popup */
.custom-dialog {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.custom-dialog-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.custom-dialog-header,
.custom-dialog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-dialog-header .close {
    cursor: pointer;
    font-size: 1.5rem;
}

.custom-dialog-footer {
    justify-content: center;
}

.custom-dialog-footer button {
    margin: 0 10px;
    padding: 10px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    color: white;
}

.custom-dialog-footer .confirm {
    background-color: #28a745;
}

.custom-dialog-footer .confirm:hover {
    background-color: #218838;
}

.custom-dialog-footer .cancel {
    background-color: #dc3545;
}

.custom-dialog-footer .cancel:hover {
    background-color: #c82333;
}
</style>

<div id="container-prorrog" style="display:none; margin:5px">
    <hr>
    <h6>Cadastrar dados de prorrogação</h7>
        <input type="hidden" name="type" value="create">
        <div class="form-group col-sm-1">
            <?php
            $a = ($gestaoIdMax[0]);
            $ultimoReg = ($a["ultimoReg"]) + 1;
            extract($findMaxProInt);
            ?>
            <div>
                <input type="hidden" class="form-control" id="fk_internacao_pror" name="fk_internacao_pror"
                    value="<?= $ultimoReg ?>">
            </div>

        </div>
        <div class="form-group col-sm-2">
            <input type="hidden" readonly class="form-control" id="data_inter_int2"
                value="<?= $findMaxProInt['0']['data_intern_int'] ?? 1 ?>" name="data_inter_int2" readonly>
        </div>
        <!-- PRORROGACAO 1 -->
        <div class="form-group row">
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
                <label class="control-label" for="prorrog1_ini_pror">Data inicial </label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_ini_pror" name="prorrog1_ini_pror">
                <div class="notif-input oculto" id="notif-input1">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_fim_pror">Data final </label>
                <input type="date" class="form-control-sm form-control" id="prorrog1_fim_pror" name="prorrog1_fim_pror">
                <div class="notif-input oculto" id="notif-input2">
                    Data inválida !
                </div>
            </div>
            <div id="div_diarias_1" class="form-group col-sm-1" style="display:none">
                <label class="control-label" for="diarias_1">Diárias </label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_1" name="diarias_1">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="isol_1_pror">Isolamento</label>
                <select class="form-control-sm form-control" id="isol_1_pror" name="isol_1_pror">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic1_pror">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic1_pror" name="adic1" value="adic1">
            </div>

        </div>
        <!-- PRORROGACAO 2  -->
        <div style="display:none" id="div-prorrog2" class="form-group row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod2_pror">2ª Acomodação</label>
                <select class="form-control-sm form-control" id="acomod2_pror" name="acomod2_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog2_ini_pror">Data inicial </label>
                <input type="date" class="form-control-sm form-control" id="prorrog2_ini_pror" name="prorrog2_ini_pror">
                <div class="notif-input oculto" id="notif-input3">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog2_fim_pror">Data final </label>
                <input type="date" class="form-control-sm form-control" id="prorrog2_fim_pror" name="prorrog2_fim_pror">
                <div class="notif-input oculto" id="notif-input4">
                    Data inválida !
                </div>
            </div>
            <div id="div_diarias_2" class="form-group col-sm-1" style="display:none">
                <label class="control-label" for="diarias_2">Diárias </label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_2" name="diarias_2">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="isol_2_pror">Isolamento</label>
                <select class="form-control-sm form-control" id="isol_2_pror" name="isol_2_pror">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic2_pror">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic2_pror" name="adic2" value="adic2">
            </div>
        </div>
        <!-- PRORROGACAO 3 -->
        <div style="display:none" id="div-prorrog3" class="form-group row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod3_pror">3ª Acomodação</label>
                <select class="form-control-sm form-control" id="acomod3_pror" name="acomod3_pror">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog1_ini_pror">Data inicial </label>
                <input type="date" class="form-control-sm form-control" id="prorrog3_ini_pror" name="prorrog3_ini_pror">
                <div class="notif-input oculto" id="notif-input5">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog3_fim_pror">Data final </label>
                <input type="date" class="form-control-sm form-control" id="prorrog3_fim_pror" name="prorrog3_fim_pror">
                <div class="notif-input oculto" id="notif-input6">
                    Data inválida !
                </div>
            </div>
            <div id="div_diarias_3" class="form-group col-sm-1" style="display:none">
                <label class="control-label" for="diarias_3">Diárias </label>
                <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                    class="form-control-sm form-control" id="diarias_3" name="diarias_3">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="isol_3_pror">Isolamento</label>
                <select class="form-control-sm form-control" id="isol_3_pror" name="isol_3_pror">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>
        </div>

</div>
<!-- Popup HTML -->
<div id="customDialog" class="custom-dialog">
    <div class="custom-dialog-content">
        <div class="custom-dialog-header">
            <span id="customDialogTitle">Atenção</span>
            <span class="close" onclick="closeDialog()">&times;</span>
        </div>
        <div class="custom-dialog-body">
            <p>Deseja prorrogar mais do que 15 dias?</p>
        </div>
        <div class="custom-dialog-footer">
            <button class="confirm" onclick="confirmDialog(true)">Sim</button>
            <button class="cancel" onclick="confirmDialog(false)">Não</button>
        </div>
    </div>
</div>

<script src="js/scriptDataPror.js"></script>

<script>
// script.js

var dialogResult = false;

function openDialog() {
    document.getElementById("customDialog").style.display = "block";
}

function closeDialog() {
    document.getElementById("customDialog").style.display = "none";
}

function confirmDialog(result) {
    dialogResult = result;
    closeDialog();
    if (dialogResult) {
        // Lógica se o usuário confirmar
        console.log("Usuário confirmou.");
    } else {
        // Lógica se o usuário cancelar
        console.log("Usuário cancelou.");
    }
}

function checkDaysLimit(days) {
    if (days > 15) {
        openDialog();
        // Aguarde o resultado do diálogo antes de continuar
        return new Promise((resolve) => {
            const checkResult = setInterval(() => {
                if (document.getElementById("customDialog").style.display === "none") {
                    clearInterval(checkResult);
                    resolve(dialogResult);
                }
            }, 100);
        });
    }
    return Promise.resolve(true);
}

// Verificação da data final da prorrogação 1 com a data inicial da prorrogação 1
dataProF.addEventListener("change", async function() {
    const dataProV = dataPro.value;
    const dataProFV = dataProF.value;
    const divMsg2 = document.querySelector("#notif-input2");

    if (!isDateValid(dataProFV) || new Date(dataProV).getTime() > new Date(dataProFV).getTime()) {
        showError(dataProF, divMsg2);
    } else {
        hideError(dataProF, divMsg2);

        const diarias1 = document.getElementById("diarias_1");
        const diariasDiv1 = document.getElementById("div_diarias_1");
        const daysDifference = calculateDaysDifference(dataProV, dataProFV);

        const limitCheck = await checkDaysLimit(daysDifference);
        if (limitCheck) {
            diariasDiv1.style.display = "block";
            diarias1.value = daysDifference;
        } else {
            showError(dataProF, divMsg2);
        }
    }
});

// Repita a lógica acima para as demais verificações de datas


$(document).ready(function() {

    $('#adic1_pror').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-prorrog2').show();
            let dataProFc = document.getElementById("prorrog1_fim_pror");
            let dataPro2 = document.getElementById("prorrog2_ini_pror");
            dataPro2.value = dataProFc.value;

        } else {
            // Se não estiver marcado, oculte a div
            $('#div-prorrog2').hide();
        }
    });
    // Adicione um ouvinte de mudança ao checkbox button
    $('#adic2_pror').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-prorrog3').show();;
            let dataProFc2 = document.getElementById("prorrog2_fim_pror");
            let dataPro3 = document.getElementById("prorrog3_ini_pror");
            dataPro3.value = dataProFc2.value;

        } else {
            // Se não estiver marcado, oculte a div
            $('#div-prorrog3').hide();
        }
    });
});
</script>
<script>
var dialogResult = false;

function openDialog() {
    document.getElementById("customDialog").style.display = "block";
}

function closeDialog() {
    document.getElementById("customDialog").style.display = "none";
}

function confirmDialog(result) {
    dialogResult = result;
    closeDialog();
    if (dialogResult) {
        // Lógica se o usuário confirmar
        console.log("Usuário confirmou.");
    } else {
        // Lógica se o usuário cancelar
        console.log("Usuário cancelou.");
    }
}

function checkDaysLimit(days) {
    if (days > 15) {
        openDialog();
        // Aguarde o resultado do diálogo antes de continuar
        return new Promise((resolve) => {
            const checkResult = setInterval(() => {
                if (document.getElementById("customDialog").style.display === "none") {
                    clearInterval(checkResult);
                    resolve(dialogResult);
                }
            }, 100);
        });
    }
    return Promise.resolve(true);
}

document.getElementById("prorrog1_fim_pror").addEventListener("change", async function() {
    const dataPro = document.getElementById("prorrog1_ini_pror").value;
    const dataProF = this.value;
    const daysDifference = Math.ceil((new Date(dataProF) - new Date(dataPro)) / (1000 * 60 * 60 * 24));

    const limitCheck = await checkDaysLimit(daysDifference);
    if (limitCheck) {
        document.getElementById("diarias_1").value = daysDifference;
        document.getElementById("div_diarias_1").style.display = "block";
    } else {
        this.value = "";
    }
});

document.getElementById("prorrog2_fim_pror").addEventListener("change", async function() {
    const dataPro = document.getElementById("prorrog2_ini_pror").value;
    const dataProF = this.value;
    const daysDifference = Math.ceil((new Date(dataProF) - new Date(dataPro)) / (1000 * 60 * 60 * 24));

    const limitCheck = await checkDaysLimit(daysDifference);
    if (limitCheck) {
        document.getElementById("diarias_2").value = daysDifference;
        document.getElementById("div_diarias_2").style.display = "block";
    } else {
        this.value = "";
    }
});

document.getElementById("prorrog3_fim_pror").addEventListener("change", async function() {
    const dataPro = document.getElementById("prorrog3_ini_pror").value;
    const dataProF = this.value;
    const daysDifference = Math.ceil((new Date(dataProF) - new Date(dataPro)) / (1000 * 60 * 60 * 24));

    const limitCheck = await checkDaysLimit(daysDifference);
    if (limitCheck) {
        document.getElementById("diarias_3").value = daysDifference;
        document.getElementById("div_diarias_3").style.display = "block";
    } else {
        this.value = "";
    }
});

$(document).ready(function() {
    $('#adic1_pror').change(function() {
        if ($(this).is(':checked')) {
            $('#div-prorrog2').show();
            let dataProFc = document.getElementById("prorrog1_fim_pror");
            let dataPro2 = document.getElementById("prorrog2_ini_pror");
            dataPro2.value = dataProFc.value;
        } else {
            $('#div-prorrog2').hide();
        }
    });

    $('#adic2_pror').change(function() {
        if ($(this).is(':checked')) {
            $('#div-prorrog3').show();
            let dataProFc2 = document.getElementById("prorrog2_fim_pror");
            let dataPro3 = document.getElementById("prorrog3_ini_pror");
            dataPro3.value = dataProFc2.value;
        } else {
            $('#div-prorrog3').hide();
        }
    });
});
</script>
<!-- 
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script> -->