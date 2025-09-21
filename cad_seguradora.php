<?php
include_once("check_logado.php");
require_once("templates/header.php");
require_once("dao/seguradoraDao.php");
require_once("models/message.php");
include_once("array_dados.php");

$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);
// Receber id da seguradora
$id_seguradora = filter_input(INPUT_GET, "id_seguradora");
?>

<body>
    <div id="main-container" class="container">
        <!-- Progress bar -->
        <div class="progress mb-4">
            <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 33%;"
                aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">Etapa 1 de 3</div>
        </div>
        <div class="row">
            <!-- Multi-step form -->
            <form action="<?= $BASE_URL ?>process_seguradora.php" class="container-fluid fundo_tela_cadastros"
                method="POST" enctype="multipart/form-data" id="multi-step-form">

                <input type="hidden" name="type" value="create">
                <input type="hidden" name="deletado_seg" value="n">

                <!-- Step 1: Informações da Seguradora -->
                <div id="step-1" class="step">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="seguradora_seg">Seguradora</label>
                            <input type="text" class="form-control" id="seguradora_seg" name="seguradora_seg" autofocus
                                placeholder="Digite o nome da seguradora">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="cnpj_seg">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj_seg" name="cnpj_seg"
                                oninput="mascara(this, 'cnpj')" placeholder="00.000.000/0000-00">
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-primary" id="next-1" onclick="nextStep(2)">
                        Próximo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 2: Endereço -->
                <div id="step-2" class="step" style="display:none;">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="cep_pac">CEP</label>
                            <input type="text" class="form-control" id="cep_seg" name="cep_seg"
                                onkeyup="consultarCEP(this, 'seg')" placeholder="00000-000">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="endereco_seg">Endereço</label>
                            <input readonly type="text" class="form-control" id="endereco_seg" name="endereco_seg"
                                placeholder="Rua, Avenida, etc.">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="bairro_seg">Bairro</label>
                            <input readonly type="text" class="form-control" id="bairro_seg" name="bairro_seg"
                                placeholder="Digite o bairro">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="cidade_seg">Cidade</label>
                            <input readonly type="text" class="form-control" id="cidade_seg" name="cidade_seg"
                                placeholder="Digite a cidade">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="estado_seg">Estado</label>
                            <select readonly class="form-control" id="estado_seg" name="estado_seg">
                                <option value="">Selecione o estado</option>
                                <?php foreach ($estado_sel as $estado): ?>
                                    <option value="<?= $estado ?>"><?= $estado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="numero_seg">Número</label>
                            <input type="number" class="form-control" id="numero_seg" name="numero_seg"
                                placeholder="Número do endereço">
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                        Próximo <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 3: Contato e Informações Complementares -->
                <div id="step-3" class="step" style="display:none;">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="email01_seg">Email Principal</label>
                            <input type="email" class="form-control" id="email01_seg" name="email01_seg"
                                placeholder="exemplo@dominio.com">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email02_seg">Email Alternativo</label>
                            <input type="email" class="form-control" id="email02_seg" name="email02_seg"
                                placeholder="exemplo@dominio.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="telefone01_seg">Telefone</label>
                            <input type="text" class="form-control" id="telefone01_seg" name="telefone01_seg"
                                onkeydown="return mascaraTelefone(event)" placeholder="(00) 0000-0000">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="telefone02_seg">Telefone Alternativo</label>
                            <input type="text" class="form-control" id="telefone02_seg" name="telefone02_seg"
                                onkeydown="return mascaraTelefone(event)" placeholder="(00) 0000-0000">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="ativo_seg">Ativo</label>
                            <select class="form-control" id="ativo_seg" name="ativo_seg">
                                <option value="s">Sim</option>
                                <option value="n">Não</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="coord_rh_seg">Coordenador RH</label>
                            <input type="text" class="form-control" id="coord_rh_seg" name="coord_rh_seg"
                                placeholder="Nome do Coordenador RH">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="coordenador_seg">Coordenador</label>
                            <input type="text" class="form-control" id="coordenador_seg" name="coordenador_seg"
                                placeholder="Nome do Coordenador">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contato_seg">Contato Seguradora</label>
                            <input type="text" class="form-control" id="contato_seg" name="contato_seg"
                                placeholder="Nome do contato na seguradora">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="dias_visita_seg">Dias Visita Clínica</label>
                            <input type="text" class="form-control" id="dias_visita_seg" name="dias_visita_seg"
                                placeholder="Digite os dias de visita à clínica">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="dias_visita_uti_seg">Dias Visita UTI</label>
                            <input type="text" class="form-control" id="dias_visita_uti_seg" name="dias_visita_uti_seg"
                                placeholder="Digite os dias de visita à UTI">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="valor_alto_custo_seg">Valor Alto Custo</label>
                            <input type="text" class="form-control" id="valor_alto_custo_seg"
                                name="valor_alto_custo_seg" placeholder="Valor alto custo">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="longa_permanencia_seg">Longa Permanência</label>
                            <input type="text" class="form-control" id="longa_permanencia_seg"
                                name="longa_permanencia_seg" placeholder="Longa permanência">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="intern_files">Logo</label>
                            <input type="file" class="form-control" onclick="novoArquivo()" name="logo_seg"
                                id="logo_seg" accept="image/png, image/jpeg">
                            <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>