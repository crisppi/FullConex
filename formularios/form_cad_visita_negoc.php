<div id="container-negoc" style="display:none; margin:5px">
    <hr>
    <h6 class="page-title">Cadastrar dados de prorrogação</h6>
    <form action="<?= $BASE_URL ?>process_negociacao.php" id="add-negociacao-form" method="POST"
        enctype="multipart/form-data">
        <input type="hidden" name="type" value="create">
        <div class="form-group col-sm-1">

            <!-- instanciar ultimo registro da internacao -->
            <?php
            $a = ($findMaxVis[0]); // pegar dado da ultima visita
            $ultimoReg = ($a["ultimoReg"]); ?>
            <div>
                <input type="hidden" class="form-control" readonly id="fk_id_int" value="<?= $id_internacao ?>"
                    name="fk_id_int" placeholder="<?= $id_internacao ?>">
            </div>
        </div>

        <!-- NEGOCIACAO 1 -->
        <div class="form-group row" style="display:flex">
            <div class="form-group col-sm-2">
                <label class="control-label" for="troca_de_1">Acomodação Solicitada</label>
                <select class="form-control" id="troca_de_1" name="troca_de_1">
                    <option value=""><i class="bi bi-arrow-down-short"></i>
                    </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="troca_para_1">Acomodação Liberada</label>
                <select class="form-control" style="margin-left:10px" id="troca_para_1" name="troca_para_1">
                    <option value=""></i>
                    </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="qtd_1">Qtd</label>
                <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_1"
                    name="qtd_1" min="1" max="30">
            </div>
            <div class="form-group col-sm-1">
                <label for="adicNeg1">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adicNeg1" name="adicNeg1" value="adicNeg1">
            </div>
        </div>

        <!-- NEGOCIACAO 2  -->
        <div style="display:none" id="div-negoc2" class="form-group row">
            <div class="form-group col-sm-2">
                <label class="control-label" for="troca_de_2">Acomodação Solicitada</label>
                <select class="form-control" id="troca_de_2" name="troca_de_2">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="troca_para_2">Acomodação Liberada</label>
                <select class="form-control" style="margin-left:10px" id="troca_para_2" name="troca_para_2">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="qtd_2">Qtd </label>
                <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_2"
                    name="qtd_2" min="1" max="30">
            </div>
            <div class="form-group col-sm-1">
                <label for="adicNeg2">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adicNeg2" name="adicNeg2" value="adicNeg2">
            </div>
        </div>
        <!-- NEGOCIACAO 3 -->
        <div class="form-group row" style="display:none" id="div-negoc3">
            <div class="form-group col-sm-2">
                <label class="control-label" for="troca_de_3">Acomodação Solicitada</label>
                <select class="form-control" id="troca_de_3" name="troca_de_3">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="troca_para_3">Acomodação Liberada</label>
                <select class="form-control" style="margin-left:10px" id="troca_para_3" name="troca_para_3">
                    <option value=""> </option>
                    <?php sort($dados_acomodacao, SORT_ASC);
                    foreach ($dados_acomodacao as $acomd) { ?>
                    <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" style="margin-left:10px" for="qtd_3">Qtd</label>
                <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_3"
                    name="qtd_3" min="1" max="30">
            </div>
        </div>
        <br>
    </form>

</div>
<script>
// Aguarde até que o DOM esteja carregado
$(document).ready(function() {
    // Adicione um ouvinte de mudança ao checkbox button
    $('#adicNeg1').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-negoc2').show();
        } else {
            // Se não estiver marcado, oculte a div
            $('#div-negoc2').hide();
        }
    });
});

$(document).ready(function() {
    // Adicione um ouvinte de mudança ao checkbox button
    $('#adicNeg2').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-negoc3').show();
        } else {
            // Se não estiver marcado, oculte a div
            $('#div-negoc3').hide();
        }
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>