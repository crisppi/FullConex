<?php
include_once("check_logado.php");

require_once("templates/header.php");
require_once("dao/hospitalDao.php");
require_once("models/message.php");

$hospitalDao = new HospitalDAO($conn, $BASE_URL);

// Receber id do usuário
$id_hospital = filter_input(INPUT_GET, "id_hospital");

?>
<?php include_once("array_dados.php");
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

<div class="internacao-page" id="main-container">
    <div class="internacao-page__hero">
        <div>
            <h1>Cadastrar hospital</h1>
        </div>
        <div class="hero-actions">
            <a class="hero-back-btn" href="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/hospitais', ENT_QUOTES, 'UTF-8') ?>">
                Voltar para lista
            </a>
            <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
        </div>
    </div>
    <div class="internacao-page__content">
        <form action="<?= $BASE_URL ?>process_hospital.php" id="multi-step-form" method="POST" enctype="multipart/form-data"
            class="needs-validation visible" novalidate>
            <div class="internacao-card internacao-card--general">
                <div class="internacao-card__header">
                    <div>
                        <p class="internacao-card__eyebrow">Dados do hospital</p>
                    </div>
                </div>
                <div class="internacao-card__body">
        <input type="hidden" name="type" value="create">
        <input type="hidden" name="deletado_hosp" value="n">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_hosp">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" class="form-control" id="cnpj_hosp"
                        name="cnpj_hosp" placeholder="Ex: 00.000.000/0000-00">
                    <div class="invalid-feedback">Por favor, insira um CNPJ válido.</div>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_hosp">Nome do Hospital</label>
                    <input type="text" class="form-control" id="nome_hosp" name="nome_hosp"
                        placeholder="Digite o nome do hospital">
                    <div class="invalid-feedback">Por favor, insira o nome do hospital.</div>
                </div>
            </div>
            <hr>
        </div>

        <!-- Step 2: Endereço e Localização -->
        <div id="step-2" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados de endereço</p>
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="cep_hosp">CEP</label>
                    <input type="text" onkeyup="consultarCEP(this, 'hosp')" class="form-control" id="cep_hosp"
                        name="cep_hosp" placeholder="00000-000">
                    <div class="invalid-feedback">Por favor, insira o CEP.</div>
                </div>
                <div class="form-group col-md-8 mb-3">
                    <label for="endereco_hosp">Endereço</label>
                    <input readonly type="text" class="form-control" id="endereco_hosp" name="endereco_hosp"
                        placeholder="Rua, Av, etc.">
                    <div class="invalid-feedback">Por favor, insira o endereço.</div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_hosp">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_hosp" name="bairro_hosp"
                        placeholder="Bairro">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_hosp">Cidade</label>
                    <input readonly type="text" class="form-control" id="cidade_hosp" name="cidade_hosp"
                        placeholder="Cidade">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_hosp">Estado</label>
                    <input readonly class="form-control" id="estado_hosp" name="estado_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_hosp">Número</label>
                    <input type="text" class="form-control" id="numero_hosp" name="numero_hosp"
                        placeholder="Número do endereço">
                </div>
            </div>

            <hr>
        </div>

        <!-- Step 3: Contato -->
        <div id="step-3" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados de contato</p>
            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <label for="email01_hosp">Email Principal</label>
                    <input type="email" class="form-control" id="email01_hosp" name="email01_hosp"
                        placeholder="exemplo@dominio.com">
                    <div class="invalid-feedback">Por favor, insira um email válido.</div>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <label for="email02_hosp">Email Alternativo</label>
                    <input type="email" class="form-control" id="email02_hosp" name="email02_hosp"
                        placeholder="exemplo@dominio.com">
                </div>
                <div class="form-group col-md-2 mb-3">
                    <label for="telefone01_hosp">Telefone Principal</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_hosp" name="telefone01_hosp" placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-2 mb-3">
                    <label for="telefone02_hosp">Telefone Alternativo</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_hosp" name="telefone02_hosp" placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-2 mb-3">
                    <label for="ativo_hosp">Ativo</label>
                    <select class="form-control" name="ativo_hosp">
                        <option value="s">Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>
            </div>
            <hr>
        </div>

        <!-- Step 4: Coordenadas e Responsáveis -->
        <div id="step-4" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados complementares</p>
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="coordenador_medico_hosp">Coordenador Médico</label>
                    <input type="text" class="form-control" id="coordenador_medico_hosp" name="coordenador_medico_hosp"
                        placeholder="Nome do coordenador médico">
                </div>
                <div class="form-group col-md-4 mb-3">
                    <label for="diretor_hosp">Diretor</label>
                    <input type="text" class="form-control" id="diretor_hosp" name="diretor_hosp"
                        placeholder="Nome do diretor">
                </div>
                <div class="form-group col-md-4 mb-3">
                    <label for="coordenador_fat_hosp">Coordenador de Faturamento</label>
                    <input type="text" class="form-control" id="coordenador_fat_hosp" name="coordenador_fat_hosp"
                        placeholder="Nome do coordenador de faturamento">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_hosp">Latitude</label>
                    <input type="text" class="form-control" id="latitude_hosp" name="latitude_hosp"
                        placeholder="Ex: -23.5505">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="longitude_hosp">Longitude</label>
                    <input type="text" class="form-control" id="longitude_hosp" name="longitude_hosp"
                        placeholder="Ex: -46.6333">
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
    // validacao de tamanho do arquivo de imagem
    const imagem = document.querySelector("#logo_hosp")
    // console.log(imagem);

    if (imagem) {
        imagem.addEventListener("change", function (e) {
            if (!imagem.files || !imagem.files[0]) return;
            if (imagem.files[0].size > (1024 * 1024 * 2)) {

                // Apresentar a mensagem de erro
                // alert("Tamanho máximo permitido do arquivo é 2mb.");
                var notifImagem = document.querySelector("#notifImagem");
                if (notifImagem) notifImagem.style.display = "block";

                // Limpar o campo arquivo
                imagem.value = '';
                //(imagem ? imagem.value = '' : null)
            }
        })
    }

    function novoArquivo() {
        var notifImagem = document.querySelector("#notifImagem");
        if (notifImagem) notifImagem.style.display = "none";

    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js">
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<?php
require_once("templates/footer.php");
?>
