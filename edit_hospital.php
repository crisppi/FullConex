<?php
include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/hospital.php");
require_once("dao/usuarioDao.php");
require_once("dao/hospitalDao.php");
require_once("dao/acomodacaoDao.php");
require_once("templates/header.php");

$user = new hospital();
$userDao = new UserDAO($conn, $BASE_URL);
$hospitalDao = new hospitalDAO($conn, $BASE_URL);
$acomodacaoDao = new acomodacaoDAO($conn, $BASE_URL);

// Receber id do usuário
$id_hospital = filter_input(INPUT_GET, "id_hospital");

$hospital = $hospitalDao->findById($id_hospital);
$acomodacoesHospital = $acomodacaoDao->findGeralByHospital((int) $id_hospital);
include_once("array_dados.php");


$cep_hosp = $hospital->cep_hosp;

if (!empty($cep_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $cep_hosp = preg_replace("/\D/", '', $cep_hosp);

    // Verifica se o CEP tem 8 dígitos
    if (strlen($cep_hosp) === 8) {
        // Formatação para CEP: XXXXX-XXX
        $bloco_1 = substr($cep_hosp, 0, 5); // Primeira parte do CEP
        $bloco_2 = substr($cep_hosp, 5, 3); // hospunda parte do CEP
        $cep_formatado = "$bloco_1-$bloco_2";
    } else {
        $cep_formatado = ''; // Caso o CEP não tenha 8 dígitos
    }
} else {
    $cep_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

$cnpj_hosp = $hospital->cnpj_hosp;

if (!empty($cnpj_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $cnpj_hosp = preg_replace("/\D/", '', $cnpj_hosp);

    // Verifica se o CNPJ tem 14 dígitos
    if (strlen($cnpj_hosp) === 14) {
        // Formatação para CNPJ: XX.XXX.XXX/XXXX-XX
        $bloco_1 = substr($cnpj_hosp, 0, 2);
        $bloco_2 = substr($cnpj_hosp, 2, 3);
        $bloco_3 = substr($cnpj_hosp, 5, 3);
        $bloco_4 = substr($cnpj_hosp, 8, 4);
        $dig_verificador = substr($cnpj_hosp, -2);
        $cnpj_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "/" . $bloco_4 . "-" . $dig_verificador;
    } else {
        $cnpj_formatado = ''; // Caso o CNPJ não tenha 14 dígitos
    }
} else {
    $cnpj_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

$telefone01_hosp = $hospital->telefone01_hosp;
$telefone02_hosp = $hospital->telefone02_hosp;

if (!empty($telefone01_hosp)) {
    // Remove qualquer caractere não numérico (se necessário)
    $telefone01_hosp = preg_replace("/\D/", '', $telefone01_hosp);

    // Verifica se o telefone tem 10 ou 11 dígitos
    if (strlen($telefone01_hosp) === 10) {
        // Formatação para telefone fixo: (00) 0000-0000
        $bloco_1 = substr($telefone01_hosp, 0, 2); // DDD
        $bloco_2 = substr($telefone01_hosp, 2, 4); // Primeira parte do número
        $bloco_3 = substr($telefone01_hosp, 6, 4); // hospunda parte do número
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone01_hosp) === 11) {
        // Formatação para celular: (00) 00000-0000
        $bloco_1 = substr($telefone01_hosp, 0, 2); // DDD
        $bloco_2 = substr($telefone01_hosp, 2, 5); // Primeira parte do número (5 dígitos)
        $bloco_3 = substr($telefone01_hosp, 7, 4); // hospunda parte do número
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone01_formatado = ''; // Caso o telefone não tenha 10 ou 11 dígitos
    }
} else {
    $telefone01_formatado = ''; // Não aplica formatação se o valor estiver vazio
}

// Repetir a lógica para o hospundo telefone
if (!empty($telefone02_hosp)) {
    $telefone02_hosp = preg_replace("/\D/", '', $telefone02_hosp);

    if (strlen($telefone02_hosp) === 10) {
        $bloco_1 = substr($telefone02_hosp, 0, 2);
        $bloco_2 = substr($telefone02_hosp, 2, 4);
        $bloco_3 = substr($telefone02_hosp, 6, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone02_hosp) === 11) {
        $bloco_1 = substr($telefone02_hosp, 0, 2);
        $bloco_2 = substr($telefone02_hosp, 2, 5);
        $bloco_3 = substr($telefone02_hosp, 7, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone02_formatado = '';
    }
} else {
    $telefone02_formatado = '';
}
?>
<script src="css/ocultar.css"></script>
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

    .confirm-delete-modal .modal-content {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #cfd4dc;
    }

    .confirm-delete-modal .modal-header {
        background: #8b95a5;
        color: #fff !important;
        border-bottom: 0;
        padding: 10px 14px;
    }

    .confirm-delete-modal .modal-title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: #ffffff !important;
    }

    .confirm-delete-modal .close {
        color: #fff;
        opacity: .9;
        text-shadow: none;
    }

    .confirm-delete-modal .close:hover {
        color: #fff;
        opacity: 1;
    }

    .confirm-delete-modal .modal-body {
        padding: 14px;
    }

    .confirm-delete-modal .modal-footer {
        border-top: 1px solid #e8ebf0;
    }

    #acomodacao-inline-card {
        background: #f7f5fb;
        border: 1px solid #e8def1;
        border-radius: 14px;
        padding: 14px;
    }
</style>

<div class="internacao-page" id="main-container">
    <div class="internacao-page__hero">
        <div>
            <h1>Editar hospital</h1>
        </div>
        <div class="hero-actions">
            <a class="hero-back-btn" href="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/hospitais', ENT_QUOTES, 'UTF-8') ?>">Voltar para lista</a>
            <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
        </div>
    </div>
    <div class="internacao-page__content">
    <form action="<?= $BASE_URL ?>process_hospital.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data" class="visible">
        <div class="internacao-card internacao-card--general">
            <div class="internacao-card__header">
                <div>
                    <p class="internacao-card__eyebrow">Dados do hospital</p>
                </div>
            </div>
            <div class="internacao-card__body">
        <input type="hidden" name="type" value="update">
        <input type="hidden" class="form-control" id="id_hospital" value="<?= $hospital->id_hospital ?>"
            name="id_hospital">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_hosp">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" value="<?= $cnpj_formatado ?>"
                        class="form-control" id="cnpj_hosp" name="cnpj_hosp">
                    <div class="invalid-feedback">Por favor, insira um CNPJ válido.</div>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_hosp"><span style="color:red;">*</span> Nome do Hospital</label>
                    <input type="text" class="form-control" id="nome_hosp" value="<?= $hospital->nome_hosp ?>"
                        name="nome_hosp" required>
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
                    <input type="text" value="<?= $cep_formatado ?>" onkeyup="consultarCEP(this, 'hosp')"
                        class="form-control" id="cep_hosp" name="cep_hosp">
                </div>
                <div class="form-group col-md-8 mb-3">
                    <label for="endereco_hosp">Endereço</label>
                    <input readonly type="text" class="form-control" value="<?= $hospital->endereco_hosp ?>"
                        id="endereco_hosp" name="endereco_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_hosp">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_hosp"
                        value="<?= $hospital->bairro_hosp ?>" name="bairro_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_hosp">Cidade</label>
                    <input readonly type="text" class="form-control" value="<?= $hospital->cidade_hosp ?>"
                        id="cidade_hosp" name="cidade_hosp">
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
                        value="<?= $hospital->numero_hosp ?>">
                </div>
            </div>

            <hr>
        </div>

        <!-- Step 3: Contato -->
        <div id="step-3" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados de contato</p>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_hosp">Email Principal</label>
                    <input type="email" class="form-control" value="<?= $hospital->email01_hosp ?>" id="email01_hosp"
                        name="email01_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_hosp">Email Alternativo</label>
                    <input type="email" class="form-control" value="<?= $hospital->email02_hosp ?>" id="email02_hosp"
                        name="email02_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_hosp">Telefone Principal</label>
                    <input type="text" onkeydown="mascaraTelefone(event)" maxlength="11" class="form-control"
                        id="telefone01_hosp" value="<?= $telefone01_formatado ?>" name="telefone01_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_hosp">Telefone Alternativo</label>
                    <input type="text" onkeydown="mascaraTelefone(event)" maxlength="11" class="form-control"
                        id="telefone02_hosp" value="<?= $telefone02_formatado ?>" name="telefone02_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="ativo_hosp">Ativo</label>
                    <select class="form-control" name="ativo_hosp">
                        <option value="s" <?= ($hospital->ativo_hosp == 's') ? 'selected' : '' ?>>Sim</option>
                        <option value="n" <?= ($hospital->ativo_hosp == 'n') ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>
            <hr>
        </div>

        <!-- Step 4: Coordenadas e Responsáveis -->
        <div id="step-4" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados complementares</p>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="coordenador_medico_hosp">Coordenador Médico</label>
                    <input type="text" class="form-control" value="<?= $hospital->coordenador_medico_hosp ?>"
                        id="coordenador_medico_hosp" name="coordenador_medico_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="diretor_hosp">Diretor</label>
                    <input type="text" class="form-control" value="<?= $hospital->diretor_hosp ?>" id="diretor_hosp"
                        name="diretor_hosp">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="coordenador_fat_hosp">Coordenador de Faturamento</label>
                    <input type="text" class="form-control" value="<?= $hospital->coordenador_fat_hosp ?>"
                        id="coordenador_fat_hosp" name="coordenador_fat_hosp">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="latitude_hosp">Latitude</label>
                    <input type="text" class="form-control" id="latitude_hosp" name="latitude_hosp"
                        placeholder="<?= $hospital->latitude_hosp ?>" value="<?= $hospital->latitude_hosp ?>">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="longitude_hosp">Longitude</label>
                    <input type="text" class="form-control" id="longitude_hosp" name="longitude_hosp"
                        placeholder="<?= $hospital->longitude_hosp ?>" value="<?= $hospital->longitude_hosp ?>">
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
                            <option value="<?= htmlspecialchars($acomd, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($acomd, ENT_QUOTES, 'UTF-8') ?></option>
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
                    <table class="table table-sm table-striped mb-0" id="acomodacoesTableEdit">
                        <thead>
                            <tr>
                                <th>Acomodação</th>
                                <th>Valor diária</th>
                                <th>Data contrato</th>
                                <th style="width: 140px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="acomodacoesTableBodyEdit">
                            <?php if (!empty($acomodacoesHospital)): ?>
                                <?php foreach ($acomodacoesHospital as $aco): ?>
                                    <tr data-existing-id="<?= (int) $aco['id_acomodacao'] ?>">
                                        <td><?= htmlspecialchars($aco['acomodacao_aco'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= 'R$ ' . number_format((float) ($aco['valor_aco'] ?? 0), 2, ',', '.') ?></td>
                                        <td>
                                            <?php
                                            $dtRaw = (string) ($aco['data_contrato_aco'] ?? '');
                                            $dtFmt = '-';
                                            if ($dtRaw) {
                                                $dtObj = date_create($dtRaw);
                                                if ($dtObj) $dtFmt = date_format($dtObj, 'd/m/Y');
                                            }
                                            echo htmlspecialchars($dtFmt, ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-existing">Remover</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="acomodacoesTableEmptyEdit">
                                    <td colspan="4" class="text-muted text-center">Nenhuma acomodação cadastrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="acomodacoesHiddenContainerEdit"></div>
                <div id="acomodacoesDeleteContainerEdit"></div>
            </div>

            <hr>
            <div class="d-flex align-items-center gap-2" style="margin: 15px 0">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Atualizar
                </button>
                <button type="button" class="btn btn-danger" onclick="showConfirmDelete()">
                    Deletar <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal fade confirm-delete-modal" id="modalConfirmDelete" tabindex="-1" aria-hidden="true" style="display:none;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar exclusão</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Fechar" onclick="hideConfirmDelete()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Este registro será deletado. Deseja continuar?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="hideConfirmDelete()">Não</button>
                        <button type="button" class="btn btn-danger" onclick="confirmAction()">Sim, deletar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function showConfirmDelete() {
                const modalEl = document.getElementById("modalConfirmDelete");
                if (!modalEl) return;
                modalEl.style.display = "block";
                modalEl.classList.add("show");
                modalEl.removeAttribute("aria-hidden");
                modalEl.setAttribute("aria-modal", "true");
                modalEl.setAttribute("role", "dialog");
                document.body.classList.add("modal-open");
                if (!document.getElementById("confirm-delete-backdrop")) {
                    const backdrop = document.createElement("div");
                    backdrop.id = "confirm-delete-backdrop";
                    backdrop.className = "modal-backdrop fade show";
                    backdrop.onclick = hideConfirmDelete;
                    document.body.appendChild(backdrop);
                }
            }

            function hideConfirmDelete() {
                const modalEl = document.getElementById("modalConfirmDelete");
                if (!modalEl) return;
                modalEl.classList.remove("show");
                modalEl.style.display = "none";
                modalEl.setAttribute("aria-hidden", "true");
                modalEl.removeAttribute("aria-modal");
                document.body.classList.remove("modal-open");
                const backdrop = document.getElementById("confirm-delete-backdrop");
                if (backdrop) backdrop.remove();
            }

            // Função para confirmar a exclusão
            function confirmAction() {
                hideConfirmDelete();
                // Inicia o processo de exclusão
                const form = document.getElementById("multi-step-form");
                form.action = "<?= $BASE_URL ?>process_hospital.php";

                // Adiciona campos ocultos para o processo de deletar
                const inputType = document.createElement("input");
                inputType.type = "hidden";
                inputType.name = "type";
                inputType.value = "delUpdate";
                form.appendChild(inputType);

                const inputDeleted = document.createElement("input");
                inputDeleted.type = "hidden";
                inputDeleted.name = "deletado_hos";
                inputDeleted.value = "s";
                form.appendChild(inputDeleted);

                // Envia o formulário
                form.submit();
            }
        </script>
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
        const tbody = document.getElementById('acomodacoesTableBodyEdit');
        const hiddenContainer = document.getElementById('acomodacoesHiddenContainerEdit');
        const deleteContainer = document.getElementById('acomodacoesDeleteContainerEdit');

        if (!nomeEl || !valorEl || !dataEl || !addBtn || !tbody || !hiddenContainer || !deleteContainer) return;

        let newIndex = 0;

        function onlyDigits(v) { return String(v || '').replace(/\D+/g, ''); }
        function formatCurrencyBR(v) {
            const digits = onlyDigits(v);
            if (!digits) return '';
            return 'R$ ' + (Number(digits) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function formatDateBR(v) {
            const m = String(v || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
            return m ? `${m[3]}/${m[2]}/${m[1]}` : (v || '');
        }
        function createHidden(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value || '';
            return input;
        }
        function ensureEmptyState() {
            if (tbody.querySelectorAll('tr').length === 0) {
                const empty = document.createElement('tr');
                empty.id = 'acomodacoesTableEmptyEdit';
                empty.innerHTML = '<td colspan="4" class="text-muted text-center">Nenhuma acomodação cadastrada.</td>';
                tbody.appendChild(empty);
            }
        }

        valorEl.addEventListener('input', function () {
            valorEl.value = formatCurrencyBR(valorEl.value);
        });

        tbody.querySelectorAll('.btn-remove-existing').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('tr');
                if (!row) return;
                const existingId = row.getAttribute('data-existing-id');
                if (existingId) {
                    deleteContainer.appendChild(createHidden('delete_existing_acomodacao_ids[]', existingId));
                }
                row.remove();
                ensureEmptyState();
            });
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

            const empty = document.getElementById('acomodacoesTableEmptyEdit');
            if (empty) empty.remove();

            const currentIndex = newIndex;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${nome}</td>
                <td>${valor || '-'}</td>
                <td>${formatDateBR(data) || '-'}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger">Remover</button></td>
            `;
            const rowBtn = row.querySelector('button');
            rowBtn.addEventListener('click', function () {
                row.remove();
                const wrap = hiddenContainer.querySelector('[data-index="' + currentIndex + '"]');
                if (wrap) wrap.remove();
                ensureEmptyState();
            });
            tbody.appendChild(row);

            const wrap = document.createElement('div');
            wrap.dataset.index = String(currentIndex);
            wrap.appendChild(createHidden('acomodacao_nome[]', nome));
            wrap.appendChild(createHidden('acomodacao_valor[]', valor));
            wrap.appendChild(createHidden('acomodacao_data[]', data));
            hiddenContainer.appendChild(wrap);
            newIndex += 1;

            nomeEl.value = '';
            valorEl.value = '';
            dataEl.value = '';
            nomeEl.focus();
        });
    })();

    function mascara(i) {
        var v = i.value;
        if (isNaN(v[v.length - 1])) { // impede entrar outro caractere que não seja número
            i.value = v.substring(0, v.length - 1);
            return;
        }
        i.setAttribute("maxlength", "14");
        if (v.length == 3 || v.length == 7) i.value += ".";
        if (v.length == 11) i.value += "-";
    }
</script>
<script>
    function mascara(i, t) {

        var v = i.value;

        if (isNaN(v[v.length - 1])) {
            i.value = v.substring(0, v.length - 1);
            return;
        }

        if (t == "data") {
            i.setAttribute("maxlength", "10");
            if (v.length == 2 || v.length == 5) i.value += "/";
        }

        if (t == "cpf") {
            i.setAttribute("maxlength", "14");
            if (v.length == 3 || v.length == 7) i.value += ".";
            if (v.length == 11) i.value += "-";
        }

        if (t == "cnpj") {
            i.setAttribute("maxlength", "18");
            if (v.length == 2 || v.length == 6) i.value += ".";
            if (v.length == 10) i.value += "/";
            if (v.length == 15) i.value += "-";
        }

        if (t == "cep") {
            i.setAttribute("maxlength", "9");
            if (v.length == 5) i.value += "-";
        }

        if (t == "tel") {
            if (v[0] == 12) {

                i.setAttribute("maxlength", "10");
                if (v.length == 5) i.value += "-";
                if (v.length == 0) i.value += "(";

            } else {
                i.setAttribute("maxlength", "9");
                if (v.length == 4) i.value += "-";
            }
        }
    }

    function mascaraTelefone(event) {
        let tecla = event.key;
        let telefone = event.target.value.replace(/\D+/g, "");

        if (/^[0-9]$/i.test(tecla)) {
            telefone = telefone + tecla;
            let tamanho = telefone.length;

            if (tamanho >= 12) {
                return false;
            }

            if (tamanho > 10) {
                telefone = telefone.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
            } else if (tamanho > 5) {
                telefone = telefone.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
            } else if (tamanho > 2) {
                telefone = telefone.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
            } else {
                telefone = telefone.replace(/^(\d*)/, "($1");
            }

            event.target.value = telefone;
        }

        if (!["Backspace", "Delete"].includes(tecla)) {
            return false;
        }
    }
</script>
<?php require_once("templates/footer.php"); ?>

</html>
