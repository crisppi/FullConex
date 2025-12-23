<div id="container-prorrog" class="container" style="display:none">
    <h6>Cadastrar dados de prorrogação</h6>
    <p class="page-description">Adicione informações sobre as diárias da prorrogação</p>
    <input type="hidden" name="type" value="create">
    <div class="form-group col-sm-1">
        <?php
        $a = $gestaoIdMax[0] ?? [];
        $ultimoReg = isset($a["ultimoReg"]) ? (int)$a["ultimoReg"] : 0;
        $findMaxProInt = is_array($findMaxProInt ?? null) ? $findMaxProInt : [];
        $dataInternacaoAtual = $findMaxProInt[0]['data_intern_int'] ?? '';
        ?>
        <input type="hidden" class="form-control" id="fk_internacao_pror" name="fk_internacao_pror" value="<?= $ultimoReg ?>" placeholder="Relatório da auditoria">
    </div>
    <div class="form-group col-sm-2">
        <label class="control-label" for="data_inter_int2">Data internacao</label>
        <input type="hidden" class="form-control" id="data_inter_int2" value="<?= $dataInternacaoAtual ?>" name="data_inter_int2" readonly>
    </div>
    <!-- PRORROGACAO 1 -->
    <div class="form-group row">
        <div class="form-group col-sm-2">
            <label class="control-label" for="acomod1_pror">Acomodação</label>
            <select class="form-control" id="acomod1_pror" name="acomod1_pror">
                <option value="">Selecione acomodação</option>
                <?php
                $acomodacoes = is_array($dados_acomodacao ?? null) ? $dados_acomodacao : [];
                sort($acomodacoes, SORT_ASC);
                foreach ($acomodacoes as $acomd) { ?>
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
    <div class="form-group-row">
        <div style="display:none" id="container-prog2">
            <div class="form-group col-sm-2">
                <label class="control-label" for="acomod2_pror">Acomodação</label>
                <select class="form-control" id="acomod2_pror" name="acomod2_pror">
                    <option value="">Selecione acomodação</option>
                    <?php
                    $acomodacoes = is_array($dados_acomodacao ?? null) ? $dados_acomodacao : [];
                    sort($acomodacoes, SORT_ASC);
                    foreach ($acomodacoes as $acomd) { ?>
                        <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog2_ini_pror">Data inicial (2)</label>
                <input type="date" class="form-control" id="prorrog2_ini_pror" name="prorrog2_ini_pror">
                <div class="notif-input oculto" id="notif-input3">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="prorrog2_fim_pror">Data final (2)</label>
                <input type="date" class="form-control" id="prorrog2_fim_pror" name="prorrog2_fim_pror">
                <div class="notif-input oculto" id="notif-input4">
                    Data inválida !
                </div>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="isol_2_pror">Isolamento</label>
                <select class="form-control" id="isol_2_pror" name="isol_2_pror">
                    <option value="s">Sim</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
        </div>
    </div>
    <!-- PRORROGACAO 3 -->
    <!-- <div class="form-group-row">
            <div style="display:none" id="container-prog3">
                <div class="form-group col-sm-2">
                    <label class="control-label" for="acomod3_pror">Acomodação (3)</label>
                    <select class="form-control" id="acomod3_pror" name="acomod3_pror">
                        <option value="">Selecione acomodação</option>
                        <?php
                        $acomodacoes = is_array($dados_acomodacao ?? null) ? $dados_acomodacao : [];
                        sort($acomodacoes, SORT_ASC);
                        foreach ($acomodacoes as $acomd) { ?>
                            <option value="<?= $acomd; ?>"><?= $acomd; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="prorrog3_ini_pror">Data inicial (3)</label>
                    <input type="date" class="form-control" id="prorrog3_ini_pror" name="prorrog3_ini_pror">
                    <div class="notif-input oculto" id="notif-input5">
                        Data inválida !
                    </div>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="prorrog3_fim_pror">Data final (3)</label>
                    <input type="date" class="form-control" id="prorrog3_fim_pror" name="prorrog3_fim_pror">
                    <div class="notif-input oculto" id="notif-input6">
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
        </div> -->

    <div style="margin-top: 5px">
        <div style="display: inline-block; margin-left:10px; margin-bottom:10px" class="form-group col-sm-1">
            <button onclick="mostrarGrupo2('container-prog2')" style="color:blue; font-size:0.8em; border:none; margin-top:15px; margin-right:10px" id="btn-gp1" class="bi bi-plus-square-fill edit-icon">2ª acomod</button>
        </div>
        <!-- <div style="display: inline-block; margin-left:30px" class="form-group col-sm-1">
            <button onclick="mostrarGrupo3('container-prog3')" style="color:blue; font-size:0.8em;border:none; margin-top:15px; margin-right:10px" id="btn-gp1" class="bi bi-plus-square-fill edit-icon"> 3ª acomod</button>
        </div> -->
    </div>
</div>
<script src="js/scriptDataPror.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>