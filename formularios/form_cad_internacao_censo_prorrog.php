<div id="container-prorrog" style="display:none; margin:5px">
<div class="titulo-abas">
<h7 style="font-weight: 700;">Prorrogação</h6>
</div>
    <input type="hidden" name="typeProrrog" value="createProrrog">
    <div class="form-group col-sm-1">

        <input type="hidden" class="form-control" id="fk_internacao_pror" name="fk_internacao_pror"
            value="<?= $query['0']['id_internacao']; ?>" placeholder="Relatório da auditoria">
    </div>
    <div class="form-group col-sm-2">
        <input type="hidden" class="form-control" id="data_inter_int2"
            value="<?= $findMaxProInt['0']['data_intern_int'] ?>" name="data_inter_int2" readonly>
    </div>
    <!-- PRORROGACAO 1 -->
    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod1_pror">Acomodação</label>
            <select class="form-control" id="acomod1_pror" name="acomod1_pror">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_ini_pror">Data inicial (1)</label>
            <input type="date" class="form-control" id="prorrog1_ini_pror" name="prorrog1_ini_pror">
            <div class="notif-input oculto" id="notif-input1">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_fim_pror">Data final (1)</label>
            <input type="date" class="form-control" id="prorrog1_fim_pror" name="prorrog1_fim_pror">
            <div class="notif-input oculto" id="notif-input2">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-1">
            <label class="control-label" for="isol_1_pror">Isolamento</label>
            <select class="form-control" id="isol_1_pror" name="isol_1_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
    </div>
    <!-- PRORROGACAO 2  -->
    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod2_pror">2ª Acomodação</label>
            <select class="form-control" id="acomod2_pror" name="acomod2_pror">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog2_ini_pror">Data inicial (2)</label>
            <input type="date" class="form-control" id="prorrog2_ini_pror" name="prorrog2_ini_pror">
            <div class="notif-input oculto" id="notif-input1">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog2_fim_pror">Data final (2)</label>
            <input type="date" class="form-control" id="prorrog2_fim_pror" name="prorrog2_fim_pror">
            <div class="notif-input oculto" id="notif-input2">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-1">
            <label class="control-label" for="isol_2_pror">Isolamento</label>
            <select class="form-control" id="isol_2_pror" name="isol_2_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
    </div>
    <!-- PRORROGACAO 3 -->
    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod3_pror">3ª Acomodação</label>
            <select class="form-control" id="acomod3_pror" name="acomod3_pror">
                <option value="">Selecione acomodação</option>
                <?php sort($dados_acomodacao, SORT_ASC);
                foreach ($dados_acomodacao as $acomd) { ?>
                <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog1_ini_pror">Data inicial (3)</label>
            <input type="date" class="form-control" id="prorrog3_ini_pror" name="prorrog3_ini_pror">
            <div class="notif-input oculto" id="notif-input2">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-2">
            <label class="control-label" for="prorrog3_fim_pror">Data final (3)</label>
            <input type="date" class="form-control" id="prorrog3_fim_pror" name="prorrog3_fim_pror">
            <div class="notif-input oculto" id="notif-input3">
                Data inválida !
            </div>
        </div>
        <div class="form-group col-sm-1">
            <label class="control-label" for="isol_3_pror">Isolamento</label>
            <select class="form-control" id="isol_3_pror" name="isol_3_pror">
                <option value="n">Não</option>
                <option value="s">Sim</option>
            </select>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>