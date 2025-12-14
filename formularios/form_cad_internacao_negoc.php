<style>
.form-group.row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-start;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 5px;
}

.btn {
    padding: 5px 10px;
    font-size: 0.9rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-add {
    background-color: #007bff;
    color: white;
}

.btn-remove {
    background-color: #dc3545;
    color: white;
}

#container-negoc .adicional-card {
    background: #fff;
    border-radius: 22px;
    border: 1px solid #ebe1f5;
    box-shadow: 0 12px 28px rgba(45, 18, 70, .08);
    padding: 22px 24px;
}

#container-negoc .adicional-card__header {
    display: flex;
    align-items: center;
    margin-bottom: 18px;
}

#container-negoc .adicional-card__title {
    display: flex;
    align-items: center;
    margin: 0;
    color: #2f1846;
    font-weight: 600;
}

#container-negoc .adicional-card__marker {
    width: 6px;
    height: 26px;
    border-radius: 10px;
    margin-right: 12px;
    background: linear-gradient(180deg, #b169d9, #d199ff);
}
</style>

<?php
if (!isset($dados_acomodacao) || !is_array($dados_acomodacao)) {
    $dados_acomodacao = [];
}
?>

<div id="container-negoc" style="display:none; margin:5px">
    <div class="adicional-card">
        <div class="adicional-card__header">
            <h4 class="adicional-card__title">
                <span class="adicional-card__marker"></span>
                Negociações
            </h4>
        </div>

        <input type="hidden" readonly class="form-control" id="fk_id_int" value="<?= $ultimoReg ?>" name="fk_id_int">
        <input type="hidden" class="form-control"
            value="<?= htmlspecialchars($_SESSION["id_usuario"] ?? '', ENT_QUOTES, 'UTF-8') ?>" id="fk_usuario_neg"
            name="fk_usuario_neg">

        <div id="negotiationFieldsContainer" style="margin:5px;">
            <input type="hidden" id="negociacoes_json" name="negociacoes_json" value="">

            <!-- Primeira linha (SEM botão "-") -->
            <div class="negotiation-field-container form-group row" data-initial="true">
                <div class="form-group col-sm-2">
                    <label for="tipo_negociacao">
                        Tipo Negociação
                        <span class="assist-anchor" data-assist-key="negociacao_tipo"></span>
                    </label>
                    <select name="tipo_negociacao" id="tipo_negociacao" class="form-control">
                        <option value="">Selecione</option>
                        <option value="TROCA UTI/APTO">TROCA UTI/APTO</option>
                        <option value="TROCA UTI/SEMI">TROCA UTI/SEMI</option>
                        <option value="TROCA SEMI/APTO">TROCA SEMI/APTO</option>
                        <option value="VESPERA">VESPERA</option>
                        <option value="GLOSA UTI">GLOSA UTI</option>
                        <option value="GLOSA APTO">GLOSA APTO</option>
                        <option value="GLOSA SEMI">GLOSA SEMI</option>
                        <option value="1/2 DIARIA APTO">1/2 DIARIA APTO</option>
                        <option value="TARDIA APTO">TARDIA APTO</option>
                        <option value="TARDIA UTI">TARDIA UTI</option>
                        <option value="DIARIA ADM">DIARIA ADM</option>
                    </select>
                </div>

                <div class="form-group col-sm-1">
                    <label class="control-label" for="data_inicio_negoc">Data inicial</label>
                    <input onchange="generateNegotiationsJSON()" type="date" class="form-control-sm form-control"
                        id="data_inicio_negoc" name="data_inicio_negoc">
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="data_fim_negoc">Data final</label>
                    <input onchange="generateNegotiationsJSON()" type="date" class="form-control-sm form-control"
                        id="data_fim_negoc" name="data_fim_negoc">
                </div>

                <div class="form-group col-sm-2">
                    <label for="troca_de">Acomodação Solicitada</label>
                    <select onchange="generateNegotiationsJSON()" name="troca_de" class="form-control">
                        <option value=""> </option>
                        <?php sort($dados_acomodacao, SORT_ASC);
                        foreach ($dados_acomodacao as $acomd) { ?>
                        <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-sm-2">
                    <label for="troca_para">Acomodação Liberada</label>
                    <select onchange="generateNegotiationsJSON()" name="troca_para" class="form-control">
                        <option value=""> </option>
                        <?php sort($dados_acomodacao, SORT_ASC);
                        foreach ($dados_acomodacao as $acomd) { ?>
                        <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-sm-1">
                    <label for="qtd">Quantidade</label>
                    <input onchange="generateNegotiationsJSON()" type="number" name="qtd" class="form-control" min="1"
                        max="30">
                </div>

                <div class="form-group col-sm-1">
                    <label for="saving_show">Saving</label>
                    <input type="text" name="saving_show" class="form-control" readonly>
                    <input type="hidden" name="saving" class="form-control">
                </div>

                <div class="form-group col-sm-1" style="margin-top:25px;">
                    <!-- SOMENTE "+" na primeira linha -->
                    <button type="button" class="btn btn-add" onclick="addNegotiationField()">+</button>
                </div>
            </div>
        </div>

        <div id="mensagemAlerta"
            style="display:none;background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border:1px solid #f5c6cb;border-radius:4px;">
        </div>
    </div>
</div>

<script>
// ========= Helpers (JS) =========
function jsNorm(s) {
    return (s || '')
        .toString()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // tira acentos
        .toLowerCase();
}

// grupos de sinônimos por token “alvo”
const TOKEN_SYNONYMS = {
    UTI: ['uti', 'cti', 'intensiv', 'terapia intensiva'],
    APTO: ['apto', 'apart', 'apartamento'],
    SEMI: ['semi', 'semi-intens', 'semi intens', 'semiintens']
};

// encontra a melhor opção do <select> que “contém” algum sinônimo do token
function findOptionValueByToken(selectEl, token) {
    if (!selectEl || !token) return '';
    const wanted = (TOKEN_SYNONYMS[token] || [token.toLowerCase()]);
    const opts = Array.from(selectEl.options);

    // tenta match por sinônimo
    for (const opt of opts) {
        const t = jsNorm(opt.text);
        if (wanted.some(w => t.includes(w))) return opt.value;
    }
    // fallback: tenta igualdade direta por texto/valor
    const tokenNorm = jsNorm(token);
    for (const opt of opts) {
        if (jsNorm(opt.text) === tokenNorm || jsNorm(opt.value) === tokenNorm) return opt.value;
    }
    return '';
}

// aplica regra de TROCA no container atual
function setTrocaFromTipo($container) {
    const tipoSel = $container.find('select[name="tipo_negociacao"]')[0];
    if (!tipoSel) return;

    const tipoText = (tipoSel.value || '').trim().toUpperCase();
    const selDe = $container.find('select[name="troca_de"]')[0];
    const selPara = $container.find('select[name="troca_para"]')[0];

    if (!selDe || !selPara) return;

    if (tipoText.startsWith('TROCA')) {
        // parse “TROCA X/Y”
        const after = tipoText.replace(/^TROCA\s*/, '').trim(); // "UTI/APTO"
        const parts = after.split('/');
        const FROM = (parts[0] || '').trim(); // "UTI"
        const TO = (parts[1] || '').trim(); // "APTO"

        const valDe = findOptionValueByToken(selDe, FROM);
        const valPara = findOptionValueByToken(selPara, TO);

        if (valDe) selDe.value = valDe;
        if (valPara) selPara.value = valPara;

        // dispara change para recalcular saving/JSON
        selDe.dispatchEvent(new Event('change'));
        selPara.dispatchEvent(new Event('change'));
    } else {
        // não é TROCA: não força, apenas sai (ou limpe se preferir)
        // selDe.value = '';
        // selPara.value = '';
    }
}

// ========= Saving =========
function calculateSaving(container) {
    // Se quiser valores por acomodação, adicione data-valor nas <option>. Sem isso, saving fica 0.
    const trocaDe = parseFloat(container.find('select[name="troca_de"] option:selected').data('valor') || 0);
    const trocaPara = parseFloat(container.find('select[name="troca_para"] option:selected').data('valor') || 0);
    const quantidade = parseInt(container.find('input[name="qtd"]').val(), 10) || 0;

    const tipoNegociacao = (container.find('select[name="tipo_negociacao"] option:selected').text() || '')
        .toUpperCase().trim();

    let saving = 0;
    if (tipoNegociacao.startsWith("TROCA")) {
        saving = (trocaDe - trocaPara) * quantidade;
    } else if (tipoNegociacao.includes("1/2 DIARIA")) {
        saving = quantidade * (trocaDe / 2);
    } else {
        saving = quantidade * trocaDe;
    }

    container.find('input[name="saving"]').val(saving.toFixed(2));
    container.find('input[name="saving_show"]')
        .val(saving >= 0 ? `R$ ${saving.toFixed(2)}` : `-R$ ${Math.abs(saving).toFixed(2)}`)
        .css('color', saving >= 0 ? 'green' : 'red');
}

// ========= Add/Remove linhas =========
function addNegotiationField() {
    const negotiationContainer = $('#negotiationFieldsContainer');
    const newField = `
    <div class="negotiation-field-container form-group row">
      <input type="hidden" readonly class="form-control" name="fk_id_int" value="<?= $ultimoReg ?>">
      <input type="hidden" name="negociacoes_json" value="">

      <div class="form-group col-sm-2">
        <label>Tipo Negociação <span class="assist-anchor" data-assist-key="negociacao_tipo"></span></label>
        <select name="tipo_negociacao" class="form-control">
          <option value="">Selecione</option>
          <option value="TROCA UTI/APTO">TROCA UTI/APTO</option>
          <option value="TROCA UTI/SEMI">TROCA UTI/SEMI</option>
          <option value="TROCA SEMI/APTO">TROCA SEMI/APTO</option>
          <option value="VESPERA">VESPERA</option>
          <option value="GLOSA UTI">GLOSA UTI</option>
          <option value="GLOSA APTO">GLOSA APTO</option>
          <option value="GLOSA SEMI">GLOSA SEMI</option>
          <option value="1/2 DIARIA APTO">1/2 DIARIA APTO</option>
          <option value="TARDIA APTO">TARDIA APTO</option>
          <option value="TARDIA UTI">TARDIA UTI</option>
          <option value="DIARIA ADM">DIARIA ADM</option>
        </select>
      </div>

      <div class="form-group col-sm-1">
        <label class="control-label">Data inicial</label>
        <input onchange="generateNegotiationsJSON()" type="date" class="form-control-sm form-control" name="data_inicio_negoc">
      </div>
      <div class="form-group col-sm-1">
        <label class="control-label">Data final</label>
        <input onchange="generateNegotiationsJSON()" type="date" class="form-control-sm form-control" name="data_fim_negoc">
      </div>

      <div class="form-group col-sm-2">
        <label>Acomodação Solicitada</label>
        <select onchange="generateNegotiationsJSON()" name="troca_de" class="form-control">
          <option value=""> </option>
          <?php sort($dados_acomodacao, SORT_ASC);
            foreach ($dados_acomodacao as $acomd) { ?>
            <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group col-sm-2">
        <label>Acomodação Liberada</label>
        <select onchange="generateNegotiationsJSON()" name="troca_para" class="form-control">
          <option value=""> </option>
          <?php sort($dados_acomodacao, SORT_ASC);
            foreach ($dados_acomodacao as $acomd) { ?>
            <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group col-sm-1">
        <label>Quantidade</label>
        <input type="number" onchange="generateNegotiationsJSON()" name="qtd" class="form-control" min="1" max="30">
      </div>

      <div class="form-group col-sm-1">
        <label>Saving</label>
        <input type="text" name="saving_show" class="form-control" readonly>
        <input type="hidden" name="saving" class="form-control">
      </div>

      <div class="form-group col-sm-1" style="margin-top:25px;">
        <button type="button" class="btn btn-add" onclick="addNegotiationField()">+</button>
        <button type="button" class="btn btn-remove" onclick="removeNegotiationField(this)">-</button>
      </div>
    </div>`;
    negotiationContainer.append(newField);
    generateNegotiationsJSON();
}

function removeNegotiationField(button) {
    $(button).closest('.negotiation-field-container').remove();
    generateNegotiationsJSON();
}

// ========= JSON / validações =========
function generateNegotiationsJSON() {
    const fkIdInt = document.getElementById("fk_id_int")?.value || "";
    const fkUsuarioNeg = document.getElementById("fk_usuario_neg")?.value || "";
    const negotiationContainers = document.querySelectorAll(".negotiation-field-container");

    const negotiations = Array.from(negotiationContainers).map((container) => {
        const trocaDeSelect = container.querySelector('[name="troca_de"]');
        const trocaParaSelect = container.querySelector('[name="troca_para"]');
        const qtdInput = container.querySelector('[name="qtd"]');
        const savingInput = container.querySelector('[name="saving"]');
        const tipoNegociacao = container.querySelector('[name="tipo_negociacao"]');
        const dataInicioNegoc = container.querySelector('[name="data_inicio_negoc"]');
        const dataFimNegoc = container.querySelector('[name="data_fim_negoc"]');

        const valorTrocaDe = trocaDeSelect?.value || null;
        const valorTrocaPara = trocaParaSelect?.value || null;
        const qtd = qtdInput ? parseInt(qtdInput.value, 10) : null;
        const saving = savingInput ? parseFloat(savingInput.value || '0') : 0;

        const tipo = tipoNegociacao?.value || null;
        const dataInicio = dataInicioNegoc?.value || null;
        const dataFim = dataFimNegoc?.value || null;

        if (!tipo || !dataInicio || !dataFim || !qtd) return null;

        return {
            fk_id_int: fkIdInt,
            fk_usuario_neg: fkUsuarioNeg,
            troca_de: valorTrocaDe,
            troca_para: valorTrocaPara,
            qtd: qtd,
            saving: saving,
            tipo_negociacao: tipo,
            data_inicio_negoc: dataInicio,
            data_fim_negoc: dataFim
        };
    }).filter(Boolean);

    const uniqueNegotiations = Array.from(new Set(negotiations.map(JSON.stringify))).map(JSON.parse);
    document.getElementById("negociacoes_json").value = JSON.stringify(uniqueNegotiations);
}

function exibirAlerta(msg) {
    const alerta = document.getElementById("mensagemAlerta");
    alerta.textContent = msg;
    alerta.style.display = "block";
    setTimeout(() => {
        alerta.style.display = "none";
    }, 4000);
}

function validarTodasDatas() {
    const dataInternEl = document.getElementById("data_intern_int");
    if (!dataInternEl || !dataInternEl.value) return true;
    const dataInternacao = new Date(dataInternEl.value);
    let valido = true;

    document.querySelectorAll(".negotiation-field-container").forEach(container => {
        const dataInicioEl = container.querySelector('[name="data_inicio_negoc"]');
        const dataFimEl = container.querySelector('[name="data_fim_negoc"]');
        if (!dataInicioEl || !dataFimEl) return;

        const dataInicio = dataInicioEl.value ? new Date(dataInicioEl.value) : null;
        const dataFim = dataFimEl.value ? new Date(dataFimEl.value) : null;

        if (dataInicio && dataInicio < dataInternacao) {
            exibirAlerta("A data de início não pode ser anterior à data de internação.");
            dataInicioEl.value = "";
            valido = false;
        }
        if (dataInicio && dataFim && dataFim < dataInicio) {
            exibirAlerta("A data final não pode ser anterior à data inicial.");
            dataFimEl.value = "";
            valido = false;
        }
    });
    return valido;
}

// ========= Listeners =========
document.addEventListener('DOMContentLoaded', function() {
    // Remove "-" da primeira linha, por garantia
    const first = document.querySelector('.negotiation-field-container');
    if (first) {
        const minus = first.querySelector('.btn-remove');
        if (minus) minus.remove();
    }

    const formInternacao = document.getElementById('myForm');
    if (formInternacao) {
        formInternacao.addEventListener('submit', () => {
            generateNegotiationsJSON();
        });
    }

    // Delegação: toda vez que mudar o tipo_negociacao, aplicamos a regra de TROCA
    $(document).on('change', 'select[name="tipo_negociacao"]', function() {
        const $cont = $(this).closest('.negotiation-field-container');
        setTrocaFromTipo($cont);
        calculateSaving($cont);
        validarTodasDatas();
        generateNegotiationsJSON();
    });

    // Recalcula saving/JSON/valida datas nos demais campos
    $(document).on('change keyup',
        'select[name="troca_de"], select[name="troca_para"], input[name="qtd"], input[name="data_inicio_negoc"], input[name="data_fim_negoc"]',
        function() {
            const $cont = $(this).closest('.negotiation-field-container');
            calculateSaving($cont);
            validarTodasDatas();
            generateNegotiationsJSON();
        }
    );

    // Gera JSON no submit
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function() {
            generateNegotiationsJSON();
            setTimeout(clearNegotiationFields, 1000);
        });
    }
});

// Mantém só a 1ª linha após envio
function clearNegotiationFields() {
    document.querySelectorAll(".negotiation-field-container").forEach((container, index) => {
        if (index === 0) {
            container.querySelectorAll("input, select").forEach(el => {
                if (!el.hasAttribute("readonly")) {
                    el.value = "";
                    el.removeAttribute("disabled");
                }
            });
        } else {
            container.remove();
        }
    });
    generateNegotiationsJSON();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>

<!-- CSS do Bootstrap-Select -->
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">
<!-- JS do Bootstrap-Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>
