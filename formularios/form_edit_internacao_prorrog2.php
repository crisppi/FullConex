<?php
/*------------------------------------------------------------
 *  BLOCO — PRORROGAÇÕES DINÂMICAS (completo)
 *-----------------------------------------------------------
 * Pré-requisitos (já existentes no seu contexto):
 *   - $conn, $BASE_URL
 *   - $intern['id_internacao']
 *   - $dados_acomodacao (array de strings)
 *   - $prorList (array de linhas)
 *-----------------------------------------------------------*/

/* helper de opções */
function optAcomod(array $lista, $sel = ''): string
{
    $out = '<option value=""></option>';
    sort($lista, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($lista as $a) {
        $aEsc = htmlspecialchars((string)$a, ENT_QUOTES, 'UTF-8');
        $selA = ($a === $sel) ? ' selected' : '';
        $out .= "<option value=\"{$aEsc}\"{$selA}>{$aEsc}</option>";
    }
    return $out;
}

/* garante pelo menos 1 linha exibida */
$prorList = array_map(fn($r) => (array)$r, $prorList ?? []);
if (!$prorList) {
    $prorList[] = ['acomod' => '', 'ini' => '', 'fim' => '', 'diarias' => '', 'isolamento' => 'n'];
}
?>
<style>
/* ===================== PRORROGAÇÕES — ESTILO ===================== */
/* Linha em grade (>=768px) */
@media (min-width: 768px) {
    .pror-row .form-grid {
        display: grid;
        grid-template-columns:
            minmax(240px, 1fr)
            /* Acomodação cresce */
            160px
            /* Data inicial */
            160px
            /* Data final   */
            110px
            /* Diárias      */
            140px
            /* Isolamento   */
            110px;
        /* Botões       */
        column-gap: 12px;
        align-items: end;
    }

    .pror-row .form-group {
        margin: 0 !important;
    }

    .pror-row .form-control,
    .pror-row .btn {
        height: calc(1.5em + .5rem + 2px);
        padding: .25rem .5rem;
        font-size: .875rem;
        /* .form-control-sm */
        line-height: 1.5;
    }

    .pror-row .w-btns>.btn-group {
        display: flex;
        gap: 8px;
    }
}

/* Empilhado em telas pequenas */
@media (max-width: 767.98px) {
    .pror-row .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        row-gap: 10px;
    }
}

/* Aparência da linha */
.pror-row {
    border: 1px solid rgba(0, 0, 0, .08);
    background: #fff;
}

.pror-row label {
    font-size: .8rem;
    margin-bottom: .2rem;
    display: block;
}

.pror-row .diarias-readonly {
    text-align: center;
    font-weight: 700;
    background: #f1f3f5;
}

/* Popup rápido */
.custom-dialog {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(0, 0, 0, .4)
}

.custom-dialog-content {
    background: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, .2)
}

.custom-dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center
}

.custom-dialog-footer {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 10px
}

.custom-dialog-header .close {
    cursor: pointer;
    font-size: 1.5rem
}

.custom-dialog-footer .confirm {
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px
}

.custom-dialog-footer .cancel {
    background: #dc3545;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px
}

.custom-dialog-footer .confirm:hover {
    background: #218838
}

.custom-dialog-footer .cancel:hover {
    background: #c82333
}
</style>

<div>
    <h4 class="mb-3">Editar Prorrogação</h4>

    <!-- chaves principais -->
    <input type="hidden" name="type" value="edit_prorrogacao">
    <input type="hidden" id="fk_internacao_pror" name="fk_internacao_pror" value="<?= (int)$intern['id_internacao'] ?>">
    <input type="hidden" id="fk_usuario_pror" name="fk_usuario_pror" value="<?= (int)($_SESSION['id_usuario'] ?? 0) ?>">
    <input type="hidden" name="select_prorrog" id="select_prorrog" value="s">

    <!-- JSON oculto -->
    <input type="hidden" id="prorrogacoes_json" name="prorrogacoes_json">

    <div id="prorContainer">
        <?php foreach ($prorList as $i => $p): $idx = (int)$i; ?>
        <div class="pror-row rounded p-3 mb-2">
            <div class="form-grid">
                <div class="form-group w-acom">
                    <label>Acomodação</label>
                    <select class="form-control form-control-sm" name="pror[<?= $idx ?>][acomod]">
                        <?= optAcomod($dados_acomodacao, $p['acomod'] ?? '') ?>
                    </select>
                </div>

                <div class="form-group w-ini">
                    <label>Data inicial</label>
                    <input type="date" class="form-control form-control-sm" name="pror[<?= $idx ?>][ini]"
                        value="<?= htmlspecialchars($p['ini'] ?? '') ?>">
                </div>

                <div class="form-group w-fim">
                    <label>Data final</label>
                    <input type="date" class="form-control form-control-sm" name="pror[<?= $idx ?>][fim]"
                        value="<?= htmlspecialchars($p['fim'] ?? '') ?>">
                </div>

                <div class="form-group w-dia">
                    <label>Diárias</label>
                    <input type="text" class="form-control form-control-sm diarias-readonly"
                        name="pror[<?= $idx ?>][diarias]" value="<?= htmlspecialchars($p['diarias'] ?? '') ?>" readonly>
                </div>

                <div class="form-group w-iso">
                    <label>Isolamento</label>
                    <?php $iso = $p['isolamento'] ?? 'n'; ?>
                    <select class="form-control form-control-sm" name="pror[<?= $idx ?>][isolamento]">
                        <option value="n" <?= $iso === 'n' ? 'selected' : '' ?>>Não</option>
                        <option value="s" <?= $iso === 's' ? 'selected' : '' ?>>Sim</option>
                    </select>
                </div>

                <div class="form-group w-btns">
                    <label style="visibility:hidden">Ações</label>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm btn-add-pror">+</button>
                        <button type="button" class="btn btn-danger  btn-sm btn-del-pror">−</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <hr>
</div>

<!-- Popup de confirmação -->
<div id="customDialog" class="custom-dialog" role="dialog" aria-modal="true" aria-labelledby="dlgTitle">
    <div class="custom-dialog-content">
        <div class="custom-dialog-header">
            <span id="dlgTitle">Atenção</span>
            <span class="close" onclick="closeDialog()">&times;</span>
        </div>
        <div class="custom-dialog-body">
            <p>Deseja prorrogar por mais de 15&nbsp;dias?</p>
        </div>
        <div class="custom-dialog-footer">
            <button class="confirm" onclick="confirmDialog(true)">Sim</button>
            <button class="cancel" onclick="confirmDialog(false)">Não</button>
        </div>
    </div>
</div>

<script>
/* ===================== PRORROGAÇÕES — JS ===================== */
/* Popup */
let dialogResolve = null;

function openDialog() {
    document.getElementById('customDialog').style.display = 'block';
}

function closeDialog() {
    document.getElementById('customDialog').style.display = 'none';
}

function confirmDialog(res) {
    closeDialog();
    if (dialogResolve) dialogResolve(res);
}

function askOver15() {
    return new Promise(r => {
        dialogResolve = r;
        openDialog();
    });
}

/* Utilidades */
function diffDays(d1, d2) {
    return Math.ceil((new Date(d2) - new Date(d1)) / 86400000);
}

function reindexNames() {
    $('#prorContainer .pror-row').each(function(i) {
        $(this).find('[name]').each(function() {
            this.name = this.name.replace(/pror\[\d+]/, 'pror[' + i + ']');
        });
    });
}

function syncJson() {
    const linhas = [];
    $('#prorContainer .pror-row').each(function() {
        const $r = $(this);
        linhas.push({
            acomod: $r.find('[name$="[acomod]"]').val() || '',
            ini: $r.find('[name$="[ini]"]').val() || '',
            fim: $r.find('[name$="[fim]"]').val() || '',
            diarias: $r.find('[name$="[diarias]"]').val() || '',
            isolamento: $r.find('[name$="[isolamento]"]').val() || 'n'
        });
    });
    $('#prorrogacoes_json').val(JSON.stringify(linhas));
}

function recalcRow($row, changedName) {
    const ini = $row.find('[name$="[ini]"]').val();
    const fim = $row.find('[name$="[fim]"]').val();
    const $dia = $row.find('[name$="[diarias]"]');

    if (changedName && changedName.endsWith('[ini]')) {
        $row.find('[name$="[fim]"]').attr('min', ini || null);
    }

    if (ini && fim && new Date(fim) >= new Date(ini)) {
        const dias = diffDays(ini, fim);
        if (dias > 15) {
            askOver15().then(ok => {
                if (!ok) {
                    if (changedName && changedName.endsWith('[fim]')) {
                        $row.find('[name$="[fim]"]').val('');
                    } else if (changedName && changedName.endsWith('[ini]')) {
                        $row.find('[name$="[ini]"]').val('');
                    }
                    $dia.val('');
                    syncJson();
                } else {
                    $dia.val(dias);
                    syncJson();
                }
            });
            return;
        }
        $dia.val(dias);
    } else {
        $dia.val('');
    }
    syncJson();
}

/* Inicialização */
$(function() {
    const $container = $('#prorContainer');

    // change das datas com cálculo e popup
    $container.on('change', 'input[type="date"]', function() {
        recalcRow($(this).closest('.pror-row'), this.name);
    });

    // adicionar linha
    $container.on('click', '.btn-add-pror', function() {
        const $clone = $container.find('.pror-row').last().clone();
        $clone.find('[name]').each(function() {
            this.value = '';
        });
        $clone.find('[name$="[fim]"]').removeAttr('min');
        $container.append($clone);
        reindexNames();
        syncJson();
    });

    // remover linha (mínimo 1)
    $container.on('click', '.btn-del-pror', function() {
        if ($container.find('.pror-row').length > 1) {
            $(this).closest('.pror-row').remove();
            reindexNames();
            syncJson();
        }
    });

    // mudanças gerais (exceto data já tratada)
    $container.on('input change', 'select,input:not([type="date"])', syncJson);

    // primeiro sync (aplica min do fim se ini existir)
    $container.find('.pror-row').each(function() {
        const $row = $(this);
        const ini = $row.find('[name$="[ini]"]').val();
        if (ini) $row.find('[name$="[fim]"]').attr('min', ini);
        recalcRow($row);
    });
    syncJson();
});
</script>