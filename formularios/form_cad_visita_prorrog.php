<div id="container-prorrog" style="display:none; margin:5px">
    <hr>
    <h6 class="page-title">Cadastrar dados de prorrogação</h6>
    <input type="hidden" name="type" value="create">
    <div class="form-group row">
        <div class="form-group col-sm-1">
            <input type="hidden" class="form-control" readonly id="fk_internacao_pror" name="fk_internacao_pror"
                value="<?= $id_internacao ?>">
        </div>
        <div class="form-group col-sm-1">
            <input type="hidden" class="form-control" readonly id="fk_visita_pror" name="fk_visita_pror"
                value="<?= $visitaMax['0']['id_visita']; ?>">
        </div>
    </div>
    <div>
        <input type="text" hidden readonly class="form-control" id="data_intern_int" name="data_intern_int"
            value="<?= date("Y-m-d", strtotime($internacaoList['0']['data_intern_int'])); ?> ">
    </div> <!-- PRORROGACAO 1 -->
    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod1_pror">Acomodação</label>
            <select class="form-control" id="acomod1_pror" name="acomod1_pror">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_ini_pror">Data inicial</label>
            <input type="date" class="form-control" id="prorrog1_ini_pror" name="prorrog1_ini_pror">
            <div class="notif-input oculto" id="notif-input1">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_fim_pror">Data final</label>
            <input type="date" class="form-control" id="prorrog1_fim_pror" name="prorrog1_fim_pror">
            <div class="notif-input oculto" id="notif-input2">
                Data inválida !
            </div>
        </div>
        <div id="div_diarias_1" class="form-group col-sm-1" style="display:none">
            <label class="control-label" for="diarias_1">Diárias </label>
            <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                class="form-control" id="diarias_1" name="diarias_1">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="isol_1_pror">Isolamento</label>
            <select class="form-control" id="isol_1_pror" name="isol_1_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
        <div class="form-group col-sm-1">
            <label for="adic1">Adicionar</label><br>
            <input style="margin-left:30px" type="checkbox" id="adic1" name="adic1" value="adic1">
        </div>
    </div>
    <!-- PRORROGACAO 2  -->
    <div style="display:none" id="div-prorrog2" class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod2_pror">2ª Acomodação</label>
            <select class="form-control" id="acomod2_pror" name="acomod2_pror">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog2_ini_pror">Data inicial</label>
            <input type="date" class="form-control" id="prorrog2_ini_pror" name="prorrog2_ini_pror">
            <div class="notif-input oculto" id="notif-input3">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog2_fim_pror">Data final</label>
            <input type="date" class="form-control" id="prorrog2_fim_pror" name="prorrog2_fim_pror">
            <div class="notif-input oculto" id="notif-input4">
                Data inválida !
            </div>
        </div>
        <div id="div_diarias_2" class="form-group col-sm-1" style="display:none">
            <label class="control-label" for="diarias_2">Diárias </label>
            <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                class="form-control" id="diarias_2" name="diarias_2">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="isol_2_pror">Isolamento</label>
            <select class="form-control" id="isol_2_pror" name="isol_2_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
        <div class="form-group col-sm-1">
            <label for="adic2">Adicionar</label><br>
            <input style="margin-left:30px" type="checkbox" id="adic2" name="adic2" value="adic2">
        </div>
    </div>
    <!-- PRORROGACAO 3 -->
    <div style="display:none" id="div-prorrog3" class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod3_pror">3ª Acomodação</label>
            <select class="form-control" id="acomod3_pror" name="acomod3_pror">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_ini_pror">Data inicial</label>
            <input type="date" class="form-control" id="prorrog3_ini_pror" name="prorrog3_ini_pror">
            <div class="notif-input oculto" id="notif-input5">
                Data inválida !
            </div>
        </div>

        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog3_fim_pror">Data final</label>
            <input type="date" class="form-control" id="prorrog3_fim_pror" name="prorrog3_fim_pror">
            <div class="notif-input oculto" id="notif-input6">
                Data inválida !
            </div>
        </div>
        <div id="div_diarias_3" class="form-group col-sm-1" style="display:none">
            <label class="control-label" for="diarias_3">Diárias </label>
            <input type="text" style="text-align:center; font-weight:600; background-color:darkgray" readonly
                class="form-control" id="diarias_3" name="diarias_3">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="isol_3_pror">Isolamento</label>
            <select class="form-control" id="isol_3_pror" name="isol_3_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
    </div>

</div>
<script src="js/scriptDataProrVisita.js"></script>
<script>
// Aguarde até que o DOM esteja carregado
$(document).ready(function() {
    // Adicione um ouvinte de mudança ao checkbox button
    $('#adic1').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-prorrog2').show();
        } else {
            // Se não estiver marcado, oculte a div
            $('#div-prorrog2').hide();
        }
    });
});

$(document).ready(function() {
    // Adicione um ouvinte de mudança ao checkbox button
    $('#adic2').change(function() {
        // Verifique se o checkbox button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-prorrog3').show();
        } else {
            // Se não estiver marcado, oculte a div
            $('#div-prorrog3').hide();
        }
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>