<div id="container-negoc" style="display:none; margin:5px">
    <hr>
    <h7 class="page-title">Adicione informações sobre as negociações</h7>
    <input type="hidden" name="type" value="create">
    <div class="form-group col-sm-1">

        <!-- instanciar ultimo registro da internacao -->
        <?php
        $a = ($gestaoIdMax[0]);
        $ultimoReg = ($a["ultimoReg"]) + 1;
        $lastId = $ultimoReg;
        // $negociacaoGeral = $negociacao->findGeral();
        $findMaxInt = $negociacaoLast->findByLastId($lastId ?? 1);

        ?>
        <input type="hidden" readonly class="form-control" id="fk_id_int" name="fk_id_int" value="<?= $ultimoReg ?>">
        <input type="hidden" class="form-control" value="<?= $_SESSION["id_usuario"] ?>" id="fk_usuario_neg"
            name="fk_usuario_neg">

    </div>

    <!-- NEGOCIACAO 1 -->
    <div class="form-group row" style="display:flex">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_1">Acomodação Solicitada</label>
            <select class="form-control-sm form-control" id="troca_de_1" name="troca_de_1">
                <option value=""><i class="bi bi-arrow-down-short"></i>
                </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_1">Acomodação Liberada</label>
            <select class="form-control-sm form-control" style="margin-left:10px" id="troca_para_1" name="troca_para_1">
                <option value=""></i>
                </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_1">Qtd</label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control-sm form-control"
                id="qtd_1" name="qtd_1" min="1" max="30">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="saving_1_show">Saving</label>
            <input type="text" disabled name="saving_1_show" style="margin-left:10px" style="font-size:0.8em"
                class="form-control-sm form-control" id="saving_1_show">
        </div>
        <input type="text" hidden name="saving_1" style="margin-left:10px" style="font-size:0.8em" class="form-control"
            id="saving_1">
        <div class="form-group col-sm-1">
            <label for="adicNeg1">Adicionar</label><br>
            <input style="margin-left:30px" type="checkbox" id="adicNeg1" name="adicNeg1" value="adicNeg1">
        </div>
    </div>

    <!-- NEGOCIACAO 2  -->
    <div style="display:none" id="div-negoc2" class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_2">Acomodação Solicitada</label>
            <select class="form-control-sm form-control" id="troca_de_2" name="troca_de_2">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_2">Acomodação Liberada</label>
            <select class="form-control-sm form-control" style="margin-left:10px" id="troca_para_2" name="troca_para_2">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_2">Qtd </label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control-sm form-control"
                id="qtd_2" name="qtd_2" min="1" max="30">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="saving_2_show">Saving</label>
            <input type="text" disabled name="saving_2_show" style="margin-left:10px" style="font-size:0.8em"
                class="form-control-sm form-control" id="saving_2_show">
        </div>
        <input type="text" hidden name="saving_2" style="margin-left:10px" style="font-size:0.8em"
            class="form-control-sm form-control" id="saving_2">
        <div class="form-group col-sm-1">
            <label for="adicNeg2">Adicionar</label><br>
            <input style="margin-left:30px" type="checkbox" id="adicNeg2" name="adicNeg2" value="adicNeg2">
        </div>
    </div>
    <!-- NEGOCIACAO 3 -->
    <div class="form-group row" style="display:none" id="div-negoc3">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_3">Acomodação Solicitada</label>
            <select class="form-control-sm form-control" id="troca_de_3" name="troca_de_3">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_3">Acomodação Liberada</label>
            <select class="form-control-sm form-control" style="margin-left:10px" id="troca_para_3" name="troca_para_3">
                <option value=""> </option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($acomodacao as $acomd) { ?>
                <option value="<?= $acomd["id_acomodacao"]; ?>"><?= $acomd["acomodacao_aco"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_3">Qtd</label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control-sm form-control"
                id="qtd_3" name="qtd_3" min="1" max="30">
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="saving_3_show">Saving</label>
            <input type="text" disabled style="margin-left:10px" style="font-size:0.8em"
                class="form-control-sm form-control" name="saving_3_show" id="saving_3_show">
        </div>
        <input type="text" hidden style="margin-left:10px" style="font-size:0.8em" class="form-control" name="saving_3"
            id="saving_3">
    </div>

</div>
<script>
// Aguarde até que o DOM esteja carregado
$(document).ready(function() {
    // Adicione um ouvinte de mudança ao radio button
    $('#adicNeg1').change(function() {
        // Verifique se o radio button está marcado
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
    // Adicione um ouvinte de mudança ao radio button
    $('#adicNeg2').change(function() {
        // Verifique se o radio button está marcado
        if ($(this).is(':checked')) {
            // Se estiver marcado, mostre a div
            $('#div-negoc3').show();
            $('adicNeg1').style.opacity = '1';

        } else {
            // Se não estiver marcado, oculte a div
            $('#div-negoc3').hide();
            $('adicNeg1').style.opacity = '0.5';

        }
    });
});


$(document).ready(function() {
    // Adicione um ouvinte de mudança ao radio button
    $('#qtd_1').change(function() {
        // Criar objeto FormData
        var formData = new FormData();

        // Adicionar os valores ao FormData
        formData.append('de', $('#troca_de_1').val());
        formData.append('para', $('#troca_para_1').val());
        formData.append('qtd', $('#qtd_1').val());


        $.ajax({
            url: 'process_saving.php', // URL do arquivo PHP
            type: 'POST', // Método de envio
            processData: false, // Não processar os dados
            contentType: false, // Não definir o tipo de conteúdo
            data: formData, // Dados a serem enviados
            success: function(response) {
                // Obtenha uma referência para o elemento <span>
                var meuSpan = document.getElementById("saving_1");
                var meuSpanShow = document.getElementById("saving_1_show");
                if (response < 0) {
                    meuSpanShow.style.color = 'red'
                    meuSpan.value = 'R$' + response;
                } else {
                    meuSpanShow.style.color = 'green'
                    meuSpan.value = 'R$' + response;
                }
                meuSpanShow.value = 'R$' + response;
                // Manipular a resposta aqui, se necessário
            },
            error: function() {
                console.log("error")
            }
        });
    });

    $('#qtd_2').change(function() {
        // Criar objeto FormData
        var formData = new FormData();

        // Adicionar os valores ao FormData
        formData.append('de', $('#troca_de_2').val());
        formData.append('para', $('#troca_para_2').val());
        formData.append('qtd', $('#qtd_2').val());


        $.ajax({
            url: 'process_saving.php', // URL do arquivo PHP
            type: 'POST', // Método de envio
            processData: false, // Não processar os dados
            contentType: false, // Não definir o tipo de conteúdo
            data: formData, // Dados a serem enviados
            success: function(response) {
                // Obtenha uma referência para o elemento <span>
                var meuSpan = document.getElementById("saving_2");
                var meuSpanShow = document.getElementById("saving_2_show");
                if (response < 0) {
                    meuSpanShow.style.color = 'red'
                    meuSpan.value = 'R$' + response;
                } else {
                    meuSpanShow.style.color = 'green'
                    meuSpan.value = 'R$' + response;
                }
                meuSpanShow.value = 'R$' + response;
                // Manipular a resposta aqui, se necessário
            },
            error: function() {
                console.log("error")
            }
        });
    });

    $('#qtd_3').change(function() {
        // Criar objeto FormData
        var formData = new FormData();

        // Adicionar os valores ao FormData
        formData.append('de', $('#troca_de_3').val());
        formData.append('para', $('#troca_para_3').val());
        formData.append('qtd', $('#qtd_3').val());


        $.ajax({
            url: 'process_saving.php', // URL do arquivo PHP
            type: 'POST', // Método de envio
            processData: false, // Não processar os dados
            contentType: false, // Não definir o tipo de conteúdo
            data: formData, // Dados a serem enviados
            success: function(response) {
                // Obtenha uma referência para o elemento <span>
                var meuSpan = document.getElementById("saving_3");
                var meuSpanShow = document.getElementById("saving_3_show");
                if (response < 0) {
                    meuSpanShow.style.color = 'red'
                    meuSpan.value = 'R$' + response;
                } else {
                    meuSpanShow.style.color = 'green'
                    meuSpan.value = 'R$' + response;
                }
                meuSpanShow.value = 'R$' + response;
                // Manipular a resposta aqui, se necessário
            },
            error: function() {
                console.log("error")
            }
        });
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>