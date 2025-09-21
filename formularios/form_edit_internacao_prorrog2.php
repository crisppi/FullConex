<?php
/*------------------------------------------------------------
 *  BLOCO — PRORROGAÇÕES DINÂMICAS
 *-----------------------------------------------------------
 *  Pré-requisitos antes deste bloco:
 *      $conn, $BASE_URL              → conexão e URL base
 *      $intern['id_internacao']      → id da internação
 *      $dados_acomodacao (array)     → lista de acomodações
 *      prorrogacaoDAO                → DAO com selectProrByIntern()
 */


/* helper p/ opções de acomodação */
function optAcomod(array $lista, $sel = ''): string
{
    $out = '<option value=""></option>';
    sort($lista, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($lista as $a) {
        $out .= '<option value="' . $a . '" ' . ($a === $sel ? 'selected' : '') . '>' . $a . '</option>';
    }
    return $out;
}
?>

<!-- ------------ CSS local (popup) ----------------------------------- -->
<style>
    .custom-dialog {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
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

    .custom-dialog-header,
    .custom-dialog-footer {
        display: flex;
        justify-content: space-between;
        align-items: center
    }

    .custom-dialog-header .close {
        cursor: pointer;
        font-size: 1.5rem
    }

    .custom-dialog-footer {
        justify-content: center
    }

    .custom-dialog-footer button {
        margin: 0 10px;
        padding: 10px 20px;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background .3s;
        color: #fff
    }

    .custom-dialog-footer .confirm {
        background: #28a745
    }

    .custom-dialog-footer .confirm:hover {
        background: #218838
    }

    .custom-dialog-footer .cancel {
        background: #dc3545
    }

    .custom-dialog-footer .cancel:hover {
        background: #c82333
    }
</style>

<!-- ------------- CONTAINER ------------------------------------------ -->
<div>
    <h4 class="mb-3">Editar Prorrogação</h4>

    <!-- chaves principais -->
    <input type="hidden" name="type" value="edit_prorrogacao">
    <input type="hidden" id="fk_internacao_pror" name="fk_internacao_pror" value="<?= $intern['id_internacao'] ?>">
    <input type="hidden" id="fk_usuario_pror" value="<?= $_SESSION['id_usuario'] ?>" name="fk_usuario_pror">
    <input type="hidden" name="select_prorrog" id="select_prorrog" value="s">
    <!-- JSON oculto que vai pro servidor -->
    <input type="hidden" id="prorrogacoes_json" name="prorrogacoes_json">

    <div id="prorContainer">
        <?php foreach ($prorList as $i => $p):
            $idx = (int) $i; ?>
            <div class="pror-row rounded p-3 mb-2">
                <div class="form-row align-items-end">

                    <div class="form-group col-sm-2">
                        <label>Acomodação</label>
                        <select class="form-control-sm form-control" name="pror[<?= $idx ?>][acomod]">
                            <?= optAcomod($dados_acomodacao, $p['acomod']) ?>
                        </select>
                    </div>

                    <div class="form-group col-sm-2">
                        <label>Data inicial</label>
                        <input type="date" class="form-control-sm form-control" name="pror[<?= $idx ?>][ini]"
                            value="<?= $p['ini'] ?>">
                    </div>

                    <div class="form-group col-sm-2">
                        <label>Data final</label>
                        <input type="date" class="form-control-sm form-control" name="pror[<?= $idx ?>][fim]"
                            value="<?= $p['fim'] ?>">
                    </div>

                    <div class="form-group col-sm-1">
                        <label>Diárias</label>
                        <input type="text" readonly
                            class="form-control-sm form-control text-center font-weight-bold bg-secondary"
                            name="pror[<?= $idx ?>][diarias]" value="<?= $p['diarias'] ?>">
                    </div>

                    <div class="form-group col-sm-1">
                        <label>Isolamento</label>
                        <select class="form-control-sm form-control" name="pror[<?= $idx ?>][isolamento]">
                            <option value="n" <?= $p['isolamento'] === 'n' ? 'selected' : '' ?>>Não</option>
                            <option value="s" <?= $p['isolamento'] === 's' ? 'selected' : '' ?>>Sim</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-1">
                        <button type="button" class="btn btn-success btn-sm btn-add-pror">+</button>
                        <button type="button" class="btn btn-danger  btn-sm btn-del-pror">−</button>
                    </div>



                </div>
            </div>
        <?php endforeach; ?>

    </div><!-- /prorContainer -->

    <hr>
</div>

<!-- ------------- POPUP --------------------------------------------- -->
<div id="customDialog" class="custom-dialog">
    <div class="custom-dialog-content">
        <div class="custom-dialog-header">
            <span>Atenção</span><span class="close" onclick="closeDialog()">&times;</span>
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
    /* ==== popup ==== */
    let dialogResolve = null;

    function openDialog() {
        document.getElementById('customDialog').style.display = 'block';
    }

    function closeDialog() {
        document.getElementById('customDialog').style.display = 'none';
    }

    function confirmDialog(res) {
        closeDialog();
        if (dialogResolve) {
            dialogResolve(res);
        }
    }

    function askOver15() {
        return new Promise(r => {
            dialogResolve = r;
            openDialog();
        });
    }

    /* ==== helpers ==== */
    function diffDays(d1, d2) {
        return Math.ceil((new Date(d2) - new Date(d1)) / 86400000); // ms -> dias
    }

    function syncJson() {
        const linhas = [];
        $('#prorContainer .pror-row').each(function () {
            const $r = $(this);
            linhas.push({
                acomod: $r.find('[name$="[acomod]"]').val() || '',
                ini: $r.find('[name$="[ini]"]').val() || '',
                fim: $r.find('[name$="[fim]"]').val() || '',
                diarias: $r.find('[name$="[diarias]"]').val() || '',
                isolamento: $r.find('[name$="[isolamento]"]').val() || 'n'
            });
        });
        console.log(JSON.stringify(linhas))
        $('#prorrogacoes_json').val(JSON.stringify(linhas));
    }

    /* ==== main ==== */
    $(function () {

        /* calcula diárias + popup */
        $('#prorContainer').on('change', 'input[type="date"]', async function () {
            const $row = $(this).closest('.pror-row');
            const ini = $row.find('[name$="[ini]"]').val();
            const fim = $row.find('[name$="[fim]"]').val();
            const $dia = $row.find('[name$="[diarias]"]');

            if (ini && fim && new Date(fim) >= new Date(ini)) {
                const dias = diffDays(ini, fim);
                if (dias > 15) {
                    const ok = await askOver15();
                    if (!ok) {
                        $(this).val('');
                        return;
                    }
                }
                $dia.val(dias);
            } else {
                $dia.val('');
            }
            syncJson();
        });

        /* adicionar linha */
        $('#prorContainer').on('click', '.btn-add-pror', function () {
            const $clone = $('#prorContainer .pror-row').last().clone();
            const idx = $('#prorContainer .pror-row').length;

            $clone.find('[name]').each(function () {
                this.name = this.name.replace(/pror\[\d+]/, 'pror[' + idx + ']');
                if (this.tagName === 'SELECT') {
                    this.value = '';
                } else {
                    this.value = '';
                }
            });
            $('#prorContainer').append($clone);
            syncJson();
        });

        /* remover linha (mínimo 1) */
        $('#prorContainer').on('click', '.btn-del-pror', function () {
            if ($('#prorContainer .pror-row').length > 1) {
                $(this).closest('.pror-row').remove();
                syncJson();
            }
        });

        /* mudanças gerais */
        $('#prorContainer').on('input change', 'input,select', syncJson);

        /* primeiro sync */
        syncJson();
    });
</script>