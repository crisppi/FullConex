<div id="container-uti" style="display:none; margin:5px">
    <h6 class="page-title">Cadastrar dados da internação em UTI</h6>
    <input type="hidden" name="typeUTI" value="createUTI">
    <!-- DADOS PARA FORMULARIO INTERNACAO UTI-->
    <div>
        <input type="hidden" class="form-control" id="id_internacao" name="id_internacao"
            value="<?= $query['0']['id_internacao']; ?> ">
    </div>
    <!-- DADOS PARA FORMULARIO UTI -->
    <div class=" form-group row">

        <div>
            <input type="hidden" class="form-control" id="fk_internacao_uti" name="fk_internacao_uti"
                value="<?= $query['0']['id_internacao']; ?> " placeholder="Relatório da auditoria">
        </div>
        <div>
            <input type="hidden" class="form-control" id="internacao_uti" name="internacao_uti" value="s">
        </div>
        <div>
            <input type="hidden" class="form-control" id="internado_uti_int" name="internado_uti_int" value="s">
        </div>

        <div class="form-group col-sm-2">
            <label for="internado_uti">Internado UTI</label>
            <select class="form-control" id="internado_uti" name="internado_uti">
                <option value="s">Sim</option>
                <option value="n">Não</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="internacao_uti">Motivo UTI</label>
            <select class="form-control" id="motivo_uti" name="motivo_uti">
                <option value=" ">Selecione o Motivo</option>
                <?php
                sort($dados_UTI, SORT_ASC);
                foreach ($dados_UTI as $uti) { ?>
                <option value="<?= $uti; ?>"><?= $uti; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="internacao_uti">Justificativa UTI</label>
            <select class="form-control" id="just_uti" name="just_uti">
                <option value="Pertinente">Pertinente</option>
                <option value="Não pertinente">Não pertinente</option>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="criterio_uti">Critério UTI</label>
            <select class="form-control" id="criterio_uti" name="criterio_uti">
                <?php
                sort($criterios_UTI, SORT_ASC);
                foreach ($criterios_UTI as $uti) { ?>
                <option value="<?= $uti; ?>"><?= $uti; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-sm-2">
            <label for="data_internacao_uti">Data internação UTI</label>
            <input type="date" class="form-control" id="data_internacao_uti"
                value="<?= $query['0']['data_intern_int']; ?>" name="data_internacao_uti">
        </div>

        <div class="form-group row">
            <div class="form-group col-sm-2">
                <label for="vm_uti">VM</label>
                <select class="form-control" id="vm_uti" name="vm_uti">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label for="dva_uti">DVA</label>
                <select class="form-control" id="dva_uti" name="dva_uti">
                    <option value="n">Não</option>
                    <option value="s">Sim</option>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label for="score_uti">Score</label>
                <select class="form-control" id="score_uti" name="score_uti">
                    <option value="">Selecione o Score</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label for="saps_uti">Saps</label>
                <select class="form-control" id="saps_uti" name="saps_uti">
                    <option value=" ">Selecione o SAPS</option>
                    <?php
                    sort($dados_saps, SORT_ASC);
                    foreach ($dados_saps as $saps) { ?>
                    <option value="<?= $saps; ?>"><?= $saps; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div style="margin-top:30px " class="form-group col-sm-2">
                <a style="color:blue; font-size:0.8em" href="https://www.rccc.eu/ppc/indicadores/saps3.html"
                    target="_blank">Calcular SAPS</a>
            </div>
        </div>
        <div>
            <label for="internacao_uti">Relatório UTI</label>
            <textarea type="textarea" rows="10" class="form-control" id="rel_uti" name="rel_uti"
                placeholder="Relatório da visita UTI"></textarea>
        </div>
    </div>
    <script>

    </script>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>