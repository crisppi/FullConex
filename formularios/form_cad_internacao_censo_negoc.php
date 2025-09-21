<div id="container-negoc" style="display:none; margin:5px">
    <h6 class="page-title">Cadastrar os dados das negociações</h6>
    <input type="hidden" name="typeNegoc" value="createNegoc">
    <div class="form-group col-sm-1">

        <!-- instanciar ultimo registro da internacao -->

        <input type="hidden" class="form-control" id="fk_id_int" name="fk_id_int"
            value="<?= $query['0']['id_internacao']; ?>" placeholder="Relatório da auditoria">
    </div>

    <!-- NEGOCIACAO 1 -->
    <div class="form-group row" style="display:flex">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_1">Acomodação Solicitada</label>
            <select class="form-control" id="troca_de_1" name="troca_de_1">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_1">Acomodação Liberada</label>
            <select class="form-control" style="margin-left:10px" id="troca_para_1" name="troca_para_1">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_1">Qtd (1)</label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_1"
                name="qtd_1" min="1" max="30">
        </div>
    </div>

    <!-- NEGOCIACAO 2  -->
    <div class="form-group row" style="display:flex">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_2">Acomodação Solicitada</label>
            <select class="form-control" id="troca_de_2" name="troca_de_2">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_2">Acomodação Liberada</label>
            <select class="form-control" style="margin-left:10px" id="troca_para_2" name="troca_para_2">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_2">Qtd (2)</label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_2"
                name="qtd_2" min="1" max="30">
        </div>
    </div>
    <!-- NEGOCIACAO 3 -->
    <div class="form-group row" style="display:flex">
        <div class="form-group col-sm-2">
            <label class="control-label" for="troca_de_3">Acomodação Solicitada</label>
            <select class="form-control" id="troca_de_3" name="troca_de_3">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="troca_para_3">Acomodação Liberada</label>
            <select class="form-control" style="margin-left:10px" id="troca_para_3" name="troca_para_3">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" style="margin-left:10px" for="qtd_3">Qtd (3)</label>
            <input type="number" style="margin-left:10px" style="font-size:0.8em" class="form-control" id="qtd_3"
                name="qtd_3" min="1" max="30">
        </div>
    </div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>