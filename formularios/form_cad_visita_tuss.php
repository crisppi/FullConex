<div id="container-tuss" style="display:none; margin:5px">
    <hr>
    <h7>Cadastrar dados do código TUSS</h7>

    <?php if (!empty($internacaoList['0']['tuss_solicitado'])): ?>

        <?php if (!empty($internacaoList['0']['tuss_solicitado'])): ?>
            <div style="background-color: #eae6f7; color: #4a235a; text-align: center; 
                    font-weight: bold; padding: 10px; border: 1px solid #dcdde1; 
                    border-top-left-radius: 5px; border-top-right-radius: 5px;">
                TUSS já liberados
            </div>

            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th scope="col">TUSS Solicitado</th>
                        <th scope="col">Quantidade Liberada</th>
                        <th scope="col">Data de Realização</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Loop para preencher os dados de TUSS -->
                    <?php
                    $hasData = false; // Variável para verificar se há dados
                    for ($i = 0; $i <= 6; $i++): ?>
                        <?php
                        $tussKey = "tuss_solicitado" . ($i > 0 ? $i : "");
                        $qtdLiberadoKey = "qtd_tuss_liberado" . ($i > 0 ? $i : "");
                        $dataRealizacaoKey = "data_realizacao_tuss" . ($i > 0 ? $i : "");

                        $tussSolicitado = $internacaoList['0'][$tussKey] ?? null;
                        $qtdLiberado = $internacaoList['0'][$qtdLiberadoKey] ?? 'Não informado';
                        $dataRealizacao = $internacaoList['0'][$dataRealizacaoKey] ?? null;

                        if ($tussSolicitado):
                            $hasData = true; // Define que há dados para exibir
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($tussSolicitado); ?></td>
                                <td><?= htmlspecialchars($qtdLiberado); ?></td>
                                <td><?= $dataRealizacao ? htmlspecialchars(date("d/m/Y", strtotime($dataRealizacao))) : 'Não informada'; ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Linha de "Nenhum dado disponível" -->
                    <?php if (!$hasData): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; font-style: italic; color: #6c757d;">Nenhum TUSS liberado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>



            </table>
        <?php endif; ?>
    <?php endif; ?>
    <input type="hidden" name="type" value="create-vis">
    <div class="form-group row">
        <input type="hidden" class="form-control" value="<?= $_SESSION["id_usuario"] ?>" id="fk_id_usuario"
            name="fk_id_usuario">
        <input type="hidden" class="form-control" id="data_create_tuss" value='<?= $agora; ?>' name="data_create_tuss">
        <div class="form-group col-sm-1">
            <input type="hidden" class="form-control" readonly id="fk_int_tuss" name="fk_int_tuss"
                value="<?= $id_internacao ?>">
        </div>
        <div class="form-group col-sm-1">
            <input type="hidden" class="form-control" readonly id="fk_vis_tuss" name="fk_vis_tuss"
                value="<?= ($visitaMax['0']['id_visita']);  ?>">
        </div>
    </div>
    <!-- TUSS -->
    <div class="form-group row">
        <!-- bloco 1 -->
        <div class="form-group row">

            <input type="hidden" class="form-control" id="bloco1" name="bloco1" value="bloco1">

            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss">Descrição Tuss</label>
                <select class="form-control" id="tuss" name="tuss">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado" name="qtd_tuss_solicitado">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado" name="qtd_tuss_liberado">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn" name="tuss_liberado_sn">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic1">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic1" name="adic1" value="adic1">
            </div>
        </div>
        <!-- bloco 2-->
        <div id="div-TUSS2" style="display:none" class="form-group row">

            <input type="hidden" class="form-control" id="bloco2" name="bloco2" value="bloco2">
            <input type="hidden" class="form-control" id="fk_int_tuss2" name="fk_int_tuss2" value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss2">Descrição Tuss</label>
                <select class="form-control" id="tuss2" name="tuss2">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss2">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss2" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss2">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado2">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado2" name="qtd_tuss_solicitado2">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado2">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado2" name="qtd_tuss_liberado2">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn2">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn2" name="tuss_liberado_sn2">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic2">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic2" name="adic2" value="adic2">
            </div>
        </div>

        <!-- bloco 3-->
        <div id="div-TUSS3" style="display:none" class="form-group row">

            <input type="hidden" class="form-control" id="bloco3" name="bloco3" value="bloco3">
            <input type="hidden" class="form-control" id="fk_int_tuss3" name="fk_int_tuss3" value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss3">Descrição Tuss</label>
                <select class="form-control" id="tuss3" name="tuss3">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss3">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss3" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss3">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado3">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado3" name="qtd_tuss_solicitado3">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado3">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado3" name="qtd_tuss_liberado3">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn3">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn3" name="tuss_liberado_sn3">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic3">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic3" name="adic3" value="adic3">
            </div>
        </div>

        <!-- bloco 4-->
        <div id="div-TUSS4" style="display:none" class="form-group row">

            <input type="hidden" class="form-control" id="bloco4" name="bloco4" value="bloco4">
            <input type="hidden" class="form-control" id="fk_int_tuss4" name="fk_int_tuss4" value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss4">Descrição Tuss</label>
                <select class="form-control" id="tuss4" name="tuss4">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss4">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss4" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss4">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado4">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado4" name="qtd_tuss_solicitado4">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado4">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado4" name="qtd_tuss_liberado4">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn4">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn4" name="tuss_liberado_sn4">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic4">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic4" name="adic4" value="adic4">
            </div>
        </div>

        <!-- bloco 5-->
        <div id="div-TUSS5" style="display:none" class="form-group row">

            <input type="hidden" class="form-control" id="bloco5" name="bloco5" value="bloco5">
            <input type="hidden" class="form-control" id="fk_int_tuss5" name="fk_int_tuss5" value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss5">Descrição Tuss</label>
                <select class="form-control" id="tuss5" name="tuss5">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss5">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss5" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss5">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado5">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado5" name="qtd_tuss_solicitado5">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado5">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado5" name="qtd_tuss_liberado5">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn5">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn5" name="tuss_liberado_sn5">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label for="adic5">Adicionar</label><br>
                <input style="margin-left:30px" type="checkbox" id="adic5" name="adic5" value="adic5">
            </div>
        </div>

        <!-- bloco 6-->
        <div id="div-TUSS6" style="display:none" class="form-group row">

            <input type="hidden" class="form-control" id="bloco6" name="bloco6" value="bloco6">
            <input type="hidden" class="form-control" id="fk_int_tuss6" name="fk_int_tuss6" value="<?= $ultimoReg ?>">
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss6">Descrição Tuss</label>
                <select class="form-control" id="tuss6" name="tuss6">
                    <option value="">...</option>
                    <?php foreach ($tussGeral as $tuss) : ?>
                        <option value="<?= $tuss["cod_tuss"] ?>">
                            <?= $tuss['cod_tuss'] . " - " . $tuss["terminologia_tuss"] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-1">
                <label class="control-label" for="data_realizacao_tuss6">Data </label>
                <input type="date" class="form-control" id="data_realizacao_tuss6" value="<?php echo date('Y-m-d') ?>"
                    name="data_realizacao_tuss6">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_solicitado6">Qtd Solicitada</label>
                <input type="text" class="form-control" id="qtd_tuss_solicitado6" name="qtd_tuss_solicitado6">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="qtd_tuss_liberado6">Qtd liberada</label>
                <input type="text" class="form-control" id="qtd_tuss_liberado6" name="qtd_tuss_liberado6">
            </div>
            <div class="form-group col-sm-2">
                <label class="control-label" for="tuss_liberado_sn6">Liberado</label>
                <select class="form-control" id="tuss_liberado_sn6" name="tuss_liberado_sn6">
                    <option value="">Selecione</option>
                    <option value="s">Sim</option>
                    <option value="n">Não</option>
                </select>
            </div>

        </div>
    </div>

</div>
<script>
    // ao liberar adicionar 1
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic1').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS2').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS2').hide();
            }
        });
    });


    // ao liberar adicionar 2
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic2').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS3').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS3').hide();
            }
        });
    });


    // ao liberar adicionar 3
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic3').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS4').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS4').hide();
            }
        });
    });


    // ao liberar adicionar 4
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic4').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS5').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS5').hide();
            }
        });
    });


    // ao liberar adicionar 5
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic5').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS6').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS6').hide();
            }
        });
    });


    // ao liberar adicionar 6
    $(document).ready(function() {
        // Adicione um ouvinte de mudança ao checkbox button
        $('#adic6').change(function() {
            // Verifique se o checkbox button está marcado
            if ($(this).is(':checked')) {
                // Se estiver marcado, mostre a div
                $('#div-TUSS7').show();

            } else {
                // Se não estiver marcado, oculte a div
                $('#div-TUSS7').hide();
            }
        });
    });



    // Anexe a função ao evento change do select
    // $(document).ready(function() {
    //     $('#tuss_liberado_sn').change(funcaoAoMudarStatus);
    // });

    // $(document).ready(function() {
    //     $('#tuss_liberado_sn2').change(funcaoAoMudarStatus2);
    // });

    // $(document).ready(function() {
    //     $('#tuss_liberado_sn3').change(funcaoAoMudarStatus3);
    // });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>