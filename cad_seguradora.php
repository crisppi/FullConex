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
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/form_cad_internacao.css">
<style>
    #main-container.internacao-page {
        margin: 2px 0 0 !important;
        padding-inline: 5px !important;
        padding-top: 0 !important;
        width: auto !important;
        max-width: 100% !important;
        overflow-x: hidden;
    }

    #main-container.internacao-page .internacao-page__hero {
        margin: 0 0 6px !important;
    }

    #main-container.internacao-page .hero-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    #main-container.internacao-page .hero-back-btn {
        border-radius: 999px;
        border: 1px solid #d9c3f4;
        color: #5e2363;
        padding: 7px 14px;
        text-decoration: none;
        font-weight: 600;
        font-size: .85rem;
        background: #f4ecfb;
    }

    #main-container.internacao-page .hero-back-btn:hover {
        color: #4a1b4e;
        background: #eadcf8;
    }

    #main-container.internacao-page .internacao-card__eyebrow {
        font-weight: 700 !important;
    }

    #multi-step-form .form-control {
        min-height: 42px;
        border-radius: 8px;
    }

    #multi-step-form select.form-control {
        height: 42px;
    }
</style>

<div id="main-container" class="internacao-page">
    <div class="internacao-page__hero">
        <div>
            <h1>Cadastrar seguradora</h1>
        </div>
        <div class="hero-actions">
            <a class="hero-back-btn" href="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/seguradoras', ENT_QUOTES, 'UTF-8') ?>">
                Voltar para lista
            </a>
            <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
        </div>
    </div>
    <div class="internacao-page__content">
        <form action="<?= $BASE_URL ?>process_seguradora.php" method="POST" enctype="multipart/form-data" id="multi-step-form" class="visible">

            <div class="internacao-card internacao-card--general">
                <div class="internacao-card__header">
                    <div>
                        <p class="internacao-card__eyebrow">Dados da seguradora</p>
                    </div>
                </div>
                <div class="internacao-card__body">
                    <input type="hidden" name="type" value="create">
                    <input type="hidden" name="deletado_seg" value="n">

                    <div id="step-1" class="step">
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="seguradora_seg"><span style="color:red;">*</span> Seguradora</label>
                                <input type="text" class="form-control" id="seguradora_seg" name="seguradora_seg" required autofocus
                                    placeholder="Digite o nome da seguradora">
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="cnpj_seg">CNPJ</label>
                                <input type="text" class="form-control" id="cnpj_seg" name="cnpj_seg"
                                    oninput="mascara(this, 'cnpj')" placeholder="00.000.000/0000-00">
                            </div>
                        </div>
                        <hr>
                    </div>

                    <div id="step-2" class="step">
                        <p class="internacao-card__eyebrow mb-3">Dados de endereço</p>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="cep_seg">CEP</label>
                                <input type="text" class="form-control" id="cep_seg" name="cep_seg"
                                    onkeyup="consultarCEP(this, 'seg')" placeholder="00000-000">
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="endereco_seg">Endereço</label>
                                <input readonly type="text" class="form-control" id="endereco_seg" name="endereco_seg"
                                    placeholder="Rua, Avenida, etc.">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="bairro_seg">Bairro</label>
                                <input readonly type="text" class="form-control" id="bairro_seg" name="bairro_seg"
                                    placeholder="Digite o bairro">
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="cidade_seg">Cidade</label>
                                <input readonly type="text" class="form-control" id="cidade_seg" name="cidade_seg"
                                    placeholder="Digite a cidade">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="estado_seg">Estado</label>
                                <select readonly class="form-control" id="estado_seg" name="estado_seg">
                                    <option value="">Selecione o estado</option>
                                    <?php foreach ($estado_sel as $estado): ?>
                                        <option value="<?= $estado ?>"><?= $estado ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="numero_seg">Número</label>
                                <input type="number" class="form-control" id="numero_seg" name="numero_seg"
                                    placeholder="Número do endereço">
                            </div>
                        </div>
                        <hr>
                    </div>

                    <div id="step-3" class="step">
                        <p class="internacao-card__eyebrow mb-3">Dados de contato</p>
                        <div class="row">
                            <div class="form-group col-md-3 mb-3">
                                <label for="email01_seg">Email Principal</label>
                                <input type="email" class="form-control" id="email01_seg" name="email01_seg"
                                    placeholder="exemplo@dominio.com">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="email02_seg">Email Alternativo</label>
                                <input type="email" class="form-control" id="email02_seg" name="email02_seg"
                                    placeholder="exemplo@dominio.com">
                            </div>
                            <div class="form-group col-md-2 mb-3">
                                <label for="telefone01_seg">Telefone</label>
                                <input type="text" class="form-control" id="telefone01_seg" name="telefone01_seg"
                                    onkeydown="return mascaraTelefone(event)" placeholder="(00) 0000-0000">
                            </div>
                            <div class="form-group col-md-2 mb-3">
                                <label for="telefone02_seg">Telefone Alternativo</label>
                                <input type="text" class="form-control" id="telefone02_seg" name="telefone02_seg"
                                    onkeydown="return mascaraTelefone(event)" placeholder="(00) 0000-0000">
                            </div>
                            <div class="form-group col-md-2 mb-3">
                                <label for="ativo_seg">Ativo</label>
                                <select class="form-control" id="ativo_seg" name="ativo_seg">
                                    <option value="s">Sim</option>
                                    <option value="n">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3 mb-3">
                                <label for="coord_rh_seg">Coordenador RH</label>
                                <input type="text" class="form-control" id="coord_rh_seg" name="coord_rh_seg"
                                    placeholder="Nome do Coordenador RH">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="coordenador_seg">Coordenador</label>
                                <input type="text" class="form-control" id="coordenador_seg" name="coordenador_seg"
                                    placeholder="Nome do Coordenador">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="contato_seg">Contato Seguradora</label>
                                <input type="text" class="form-control" id="contato_seg" name="contato_seg"
                                    placeholder="Nome do contato na seguradora">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="logo_seg">Logo</label>
                                <input type="file" class="form-control" onclick="novoArquivo()" name="logo_seg"
                                    id="logo_seg" accept="image/png, image/jpeg">
                                <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3 mb-3">
                                <label for="dias_visita_seg">Dias Visita Clínica</label>
                                <input type="text" class="form-control" id="dias_visita_seg" name="dias_visita_seg"
                                    placeholder="Digite os dias de visita à clínica">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="dias_visita_uti_seg">Dias Visita UTI</label>
                                <input type="text" class="form-control" id="dias_visita_uti_seg" name="dias_visita_uti_seg"
                                    placeholder="Digite os dias de visita à UTI">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="valor_alto_custo_seg">Valor Alto Custo</label>
                                <input type="text" class="form-control" id="valor_alto_custo_seg"
                                    name="valor_alto_custo_seg" placeholder="Valor alto custo">
                            </div>
                            <div class="form-group col-md-3 mb-3">
                                <label for="longa_permanencia_seg">Longa Permanência</label>
                                <input type="text" class="form-control" id="longa_permanencia_seg"
                                    name="longa_permanencia_seg" placeholder="Longa permanência">
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Cadastrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const imagem = document.querySelector("#logo_seg");

    if (imagem) {
        imagem.addEventListener("change", function () {
            if (!imagem.files || !imagem.files[0]) return;
            if (imagem.files[0].size > (1024 * 1024 * 2)) {
                var notifImagem = document.querySelector("#notifImagem");
                if (notifImagem) notifImagem.style.display = "block";
                imagem.value = '';
            }
        });
    }

    function novoArquivo() {
        var notifImagem = document.querySelector("#notifImagem");
        if (notifImagem) notifImagem.style.display = "none";
    }
</script>

<?php
require_once("templates/footer.php");
?>
