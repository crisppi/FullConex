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

    #acomodacao-inline-card {
        background: #f7f5fb;
        border: 1px solid #e8def1;
        border-radius: 14px;
        padding: 14px;
    }

    #acomodacoesTable th,
    #acomodacoesTable td {
        vertical-align: middle;
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
                    <label for="nome_hosp"><span style="color:red;">*</span> Nome do Hospital</label>
                    <input type="text" class="form-control" id="nome_hosp" name="nome_hosp" required
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

            <p class="internacao-card__eyebrow mb-3">Acomodações do hospital</p>
            <div id="acomodacao-inline-card" class="mb-3">
                <div class="row">
                    <div class="form-group col-md-4 mb-2">
                        <label for="acomodacao_nome_inline">Acomodação</label>
                        <select class="form-control" id="acomodacao_nome_inline">
                            <option value="">Selecione</option>
                            <?php
                            sort($dados_acomodacao, SORT_ASC);
                            foreach ($dados_acomodacao as $acomd): ?>
                            <option value="<?= htmlspecialchars($acomd, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($acomd, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label for="acomodacao_valor_inline">Valor diária</label>
                        <input type="text" class="form-control" id="acomodacao_valor_inline" placeholder="R$ 0,00">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label for="acomodacao_data_inline">Data contrato</label>
                        <input type="date" class="form-control" id="acomodacao_data_inline">
                    </div>
                    <div class="form-group col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" id="btnAddAcomodacaoInline" class="btn btn-primary w-100">+</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-striped mb-0" id="acomodacoesTable">
                        <thead>
                            <tr>
                                <th>Acomodação</th>
                                <th>Valor diária</th>
                                <th>Data contrato</th>
                                <th style="width: 80px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="acomodacoesTableBody">
                            <tr id="acomodacoesTableEmpty">
                                <td colspan="4" class="text-muted text-center">Nenhuma acomodação adicionada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="acomodacoesHiddenContainer"></div>
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
    (function () {
        const nomeEl = document.getElementById('acomodacao_nome_inline');
        const valorEl = document.getElementById('acomodacao_valor_inline');
        const dataEl = document.getElementById('acomodacao_data_inline');
        const addBtn = document.getElementById('btnAddAcomodacaoInline');
        const tbody = document.getElementById('acomodacoesTableBody');
        const hiddenContainer = document.getElementById('acomodacoesHiddenContainer');
        const emptyRow = document.getElementById('acomodacoesTableEmpty');

        if (!nomeEl || !valorEl || !dataEl || !addBtn || !tbody || !hiddenContainer || !emptyRow) {
            return;
        }

        let index = 0;

        function createHidden(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value || '';
            return input;
        }

        function onlyDigits(value) {
            return String(value || '').replace(/\D+/g, '');
        }

        function formatCurrencyBR(value) {
            const digits = onlyDigits(value);
            if (!digits) return '';
            const cents = Number(digits) / 100;
            return 'R$ ' + cents.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDateBR(value) {
            const raw = String(value || '').trim();
            if (!raw) return '';
            const m = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!m) return raw;
            return `${m[3]}/${m[2]}/${m[1]}`;
        }

        function addRow(nome, valor, data) {
            if (emptyRow) emptyRow.style.display = 'none';
            const dataView = formatDateBR(data);

            const row = document.createElement('tr');
            row.dataset.index = String(index);
            row.innerHTML = `
                <td>${nome}</td>
                <td>${valor || '-'}</td>
                <td>${dataView || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger">Remover</button></td>
            `;

            const wrap = document.createElement('div');
            wrap.dataset.index = String(index);
            wrap.appendChild(createHidden('acomodacao_nome[]', nome));
            wrap.appendChild(createHidden('acomodacao_valor[]', valor));
            wrap.appendChild(createHidden('acomodacao_data[]', data));
            hiddenContainer.appendChild(wrap);

            row.querySelector('button').addEventListener('click', function () {
                row.remove();
                wrap.remove();
                if (!tbody.querySelector('tr')) {
                    emptyRow.style.display = '';
                    tbody.appendChild(emptyRow);
                }
            });

            tbody.appendChild(row);
            index += 1;
        }

        valorEl.addEventListener('input', function () {
            const formatted = formatCurrencyBR(valorEl.value);
            valorEl.value = formatted;
        });

        addBtn.addEventListener('click', function () {
            const nome = (nomeEl.value || '').trim();
            const valor = formatCurrencyBR(valorEl.value);
            const data = (dataEl.value || '').trim();

            if (!nome) {
                alert('Selecione a acomodação.');
                nomeEl.focus();
                return;
            }

            addRow(nome, valor, data);
            nomeEl.value = '';
            valorEl.value = '';
            dataEl.value = '';
            nomeEl.focus();
        });
    })();

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
