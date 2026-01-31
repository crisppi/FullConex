<div id="detalhes-card-wrapper" style="display:none;">
    <div class="detalhes-card">
        <div class="detalhes-card__header">
            <h4 class="detalhes-card__title">
                <span class="detalhes-card__marker"></span>
                Detalhes do relatório
            </h4>
        </div>

        <input type="hidden" class="form-control" id="select_detalhes" name="select_detalhes">
        <input type="hidden" id="data_create_int" value='<?= $agora; ?>' name="data_create_int">
        <div id="div-detalhado" class="form-group row" style="margin-left:-12px; display:none;">
            <div class="form-group row">
                <input type="hidden" readonly id="fk_int_det" name="fk_int_det" value="<?= ($ultimoReg + 1) ?>">

                <div class="form-group col-sm-2">
                    <label class="control-label" for="curativo_det">Curativo</label>
                    <select class="input-lg-fullcare form-control" id="curativo_det" name="curativo_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="dieta_det">Tipo dieta</label>
                    <select class="input-lg-fullcare form-control" id="dieta_det" name="dieta_det">
                        <option value=""></option>
                        <option value="Oral">Oral</option>
                        <option value="Enteral">Enteral</option>
                        <option value="NPP">NPP</option>
                        <option value="Jejum">Jejum</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="nivel_consc_det">Nível de Consciência</label>
                    <select class="input-lg-fullcare form-control" id="nivel_consc_det" name="nivel_consc_det">
                        <option value=""></option>
                        <option value="Consciente">Consciente</option>
                        <option value="Comatoso">Comatoso</option>
                        <option value="Vigil">Vigil</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="oxig_det">Oxigênio</label>
                    <select class="input-lg-fullcare form-control" id="oxig_det" name="oxig_det">
                        <option value=""></option>
                        <option value="Cateter">Cateter</option>
                        <option value="Mascara">Máscara</option>
                        <option value="VNI">VNI</option>
                        <option value="Alto Fluxo">Alto Fluxo</option>
                    </select>
                </div>
                <div id="div-oxig" class="form-group col-sm-1">
                    <label class="control-label" for="oxig_uso_det">Lts O2</label>
                    <input class="input-lg-fullcare form-control" type="text" name="oxig_uso_det">
                </div>

                <div class="form-group col-sm-3">
                    <label class="control-label">Dispositivos</label>
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="form-check ">
                            <label class="control-label" for="tqt_det">TQT</label>
                            <input class="form-check-input" type="checkbox" name="tqt_det" id="tqt_det"
                                value="TQT">
                        </div>
                        <div class="form-check">
                            <label class="control-label" for="svd_det">SVD</label>
                            <input class="form-check-input" type="checkbox" name="svd_det" id="svd_det"
                                value="SVD">
                        </div>
                        <div class="form-check" style="text-align: center;">
                            <label class="control-label" for="sne_det">SNE</label>
                            <input class="form-check-input" type="checkbox" name="sne_det" id="sne_det"
                                value="SNE">
                        </div>
                        <div class="form-check">
                            <label class="control-label" for="gtt_det">GTT</label>
                            <input class="form-check-input" type="checkbox" name="gtt_det" id="gtt_det"
                                value="GTT">
                        </div>
                        <div class="form-check">
                            <label class="control-label" for="dreno_det">Dreno</label>
                            <input class="form-check-input" type="checkbox" name="dreno_det" id="dreno_det"
                                value="Dreno">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row" style="margin-top: -20px;">
                <div class="form-group col-sm-2">
                    <label class="control-label" for="hemoderivados_det">Hemoderivados</label>
                    <select class="input-lg-fullcare form-control" id="hemoderivados_det"
                        name="hemoderivados_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="dialise_det">Diálise</label>
                    <select class="input-lg-fullcare form-control" id="dialise_det" name="dialise_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="oxigenio_hiperbarica_det">Oxigenioterapia
                        Hiperbárica</label>
                    <select class="input-lg-fullcare form-control" id="oxigenio_hiperbarica_det"
                        name="oxigenio_hiperbarica_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="qt_det">QT</label>
                    <select class="input-lg-fullcare form-control" id="qt_det" name="qt_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="rt_det">RT</label>
                    <select class="input-lg-fullcare form-control" id="rt_det" name="rt_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="acamado_det">Acamado</label>
                    <select class="input-lg-fullcare form-control" id="acamado_det" name="acamado_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="atb_det">Antibiótico</label>
                    <select class="input-lg-fullcare form-control" id="atb_det" name="atb_det">
                        <option value=""></option>
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
                <div id="atb" class="form-group col-sm-3">
                    <label class="control-label" for="atb_uso_det">Antibiótico em uso</label>
                    <input class="form-control" type="text" name="atb_uso_det">
                </div>
                <div class="form-group col-sm-1">
                    <label class="control-label" for="medic_alto_custo_det">Medicação</label>
                    <select class="input-lg-fullcare form-control" id="medic_alto_custo_det"
                        name="medic_alto_custo_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div id="medicacaoDet" class="form-group col-sm-3">
                    <label class="control-label" for="qual_medicamento_det">Medicação alto custo</label>
                    <input class="input-lg-fullcare form-control" type="text" name="qual_medicamento_det">
                </div>
                <div>
                    <label for="exames_det">Exames relevantes</label>
                    <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="3"
                        onclick="aumentarText('exames_det')" onblur="reduzirText('exames_det', 3)"
                        class="form-control" id="exames_det" name="exames_det"></textarea>
                </div>
                <div>
                    <label for="oportunidades_det">Oportunidades</label>
                    <textarea data-saude-autocomplete="true" style="resize:none" maxlength="5000" rows="2"
                        onclick="aumentarText('oportunidades_det')" class="form-control" id="oportunidades_det"
                        onblur="reduzirText('oportunidades_det', 3)" name="oportunidades_det"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group col-sm-2">
                    <label class="control-label" for="liminar_det">Possui Liminar?</label>
                    <select class="input-lg-fullcare form-control" id="liminar_det" name="liminar_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="paliativos_det">Está em Cuidados Paliativos?</label>
                    <select class="input-lg-fullcare form-control" id="paliativos_det" name="paliativos_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="parto_det">Parto</label>
                    <select class="input-lg-fullcare form-control" id="parto_det" name="parto_det">
                        <option value="n">Não</option>
                        <option value="s">Sim</option>
                    </select>
                </div>
                <div class="form-group col-sm-2">
                    <label class="control-label" for="braden_det">Escala de Braden</label>
                    <select class="input-lg-fullcare form-control" id="braden_det" name="braden_det">
                        <option value=""></option>
                        <option value="alto">Alto</option>
                        <option value="moderado">Moderado</option>
                        <option value="baixo">Baixo</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>