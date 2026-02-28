<?php
include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/estipulante.php");
require_once("dao/usuarioDao.php");
require_once("dao/estipulanteDao.php");
require_once("templates/header.php");

include_once("array_dados.php");

$user = new Estipulante();
$userDao = new UserDAO($conn, $BASE_URL);
$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

// Receber id do estipulante
$id_estipulante = filter_input(INPUT_GET, "id_estipulante");

$estipulante = $estipulanteDao->findById($id_estipulante);
$estado_selecionado = $estipulante->estado_est;

$cnpj_est = $estipulante->cnpj_est;

// Formatação CNPJ
if (!empty($cnpj_est)) {
    $cnpj_est = preg_replace("/\D/", '', $cnpj_est);
    if (strlen($cnpj_est) === 14) {
        $bloco_1 = substr($cnpj_est, 0, 2);
        $bloco_2 = substr($cnpj_est, 2, 3);
        $bloco_3 = substr($cnpj_est, 5, 3);
        $bloco_4 = substr($cnpj_est, 8, 4);
        $dig_verificador = substr($cnpj_est, -2);
        $cnpj_formatado = $bloco_1 . "." . $bloco_2 . "." . $bloco_3 . "/" . $bloco_4 . "-" . $dig_verificador;
    } else {
        $cnpj_formatado = '';
    }
} else {
    $cnpj_formatado = '';
}

$telefone01_est = $estipulante->telefone01_est;
$telefone02_est = $estipulante->telefone02_est;

if (!empty($telefone01_est)) {
    $telefone01_est = preg_replace("/\D/", '', $telefone01_est);
    if (strlen($telefone01_est) === 10) {
        $bloco_1 = substr($telefone01_est, 0, 2);
        $bloco_2 = substr($telefone01_est, 2, 4);
        $bloco_3 = substr($telefone01_est, 6, 4);
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone01_est) === 11) {
        $bloco_1 = substr($telefone01_est, 0, 2);
        $bloco_2 = substr($telefone01_est, 2, 5);
        $bloco_3 = substr($telefone01_est, 7, 4);
        $telefone01_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } else {
        $telefone01_formatado = '';
    }
} else {
    $telefone01_formatado = '';
}

if (!empty($telefone02_est)) {
    $telefone02_est = preg_replace("/\D/", '', $telefone02_est);
    if (strlen($telefone02_est) === 10) {
        $bloco_1 = substr($telefone02_est, 0, 2);
        $bloco_2 = substr($telefone02_est, 2, 4);
        $bloco_3 = substr($telefone02_est, 6, 4);
        $telefone02_formatado = "($bloco_1) $bloco_2-$bloco_3";
    } elseif (strlen($telefone02_est) === 11) {
        $bloco_1 = substr($telefone02_est, 0, 2);
        $bloco_2 = substr($telefone02_est, 2, 5);
        $bloco_3 = substr($telefone02_est, 7, 4);
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
</style>

<!-- Formulário de Edição -->
<div id="main-container" class="internacao-page">
    <div class="internacao-page__hero">
        <div><h1>Editar estipulante</h1></div>
        <div class="hero-actions">
            <a class="hero-back-btn" href="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/estipulantes', ENT_QUOTES, 'UTF-8') ?>">Voltar para lista</a>
            <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
        </div>
    </div>
    <div class="internacao-page__content">

    <form action="<?= $BASE_URL ?>process_estipulante.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data" class="needs-validation visible" novalidate>
        <div class="internacao-card internacao-card--general">
            <div class="internacao-card__header">
                <div><p class="internacao-card__eyebrow">Dados do estipulante</p></div>
            </div>
            <div class="internacao-card__body">

        <input type="hidden" name="type" value="update">
        <input type="hidden" name="id_estipulante" value="<?= $estipulante->id_estipulante ?>">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_est">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" class="form-control" id="cnpj_est"
                        name="cnpj_est" value="<?= $cnpj_formatado ?>" placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_est"><span style="color:red;">*</span> Estipulante</label>
                    <input type="text" class="form-control" id="nome_est" name="nome_est"
                        value="<?= $estipulante->nome_est ?>" placeholder="Nome do estipulante" required>
                </div>
            </div>
            <hr>
        </div>

        <!-- Step 2: Endereço -->
        <div id="step-2" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados de endereço</p>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cep_est">CEP</label>
                    <input type="text" oninput="mascara(this, 'cep')" onkeyup="consultarCEP(this, 'est')"
                        class="form-control" id="cep_est" name="cep_est" value="<?= $estipulante->cep_est ?>"
                        placeholder="00000-000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="endereco_est">Endereço</label>
                    <input type="text" class="form-control" id="endereco_est" name="endereco_est"
                        value="<?= $estipulante->endereco_est ?>" placeholder="Rua, avenida...">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_est">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_est" name="bairro_est"
                        value="<?= $estipulante->bairro_est ?>" placeholder="Bairro">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_est">Cidade</label>
                    <input readonly type="text" class="form-control" id="cidade_est" name="cidade_est"
                        value="<?= $estipulante->cidade_est ?>" placeholder="Cidade">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_est">Estado</label>
                    <input readonly value="<?= $estipulante->estado_est ?>" class="form-control" id="estado_est" name="estado_est">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_est">Número</label>
                    <input type="text" class="form-control" id="numero_est" name="numero_est"
                        value="<?= $estipulante->numero_est ?>" placeholder="Número">
                </div>
            </div>
            <hr>
        </div>

        <!-- Step 3: Contato e Finalização -->
        <div id="step-3" class="step">
            <p class="internacao-card__eyebrow mb-3">Dados de contato</p>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_est">Email Principal</label>
                    <input type="email" class="form-control" id="email01_est" name="email01_est"
                        value="<?= $estipulante->email01_est ?>" placeholder="exemplo@dominio.com">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_est">Email Alternativo</label>
                    <input type="email" class="form-control" id="email02_est" name="email02_est"
                        value="<?= $estipulante->email02_est ?>" placeholder="exemplo@dominio.com">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_est">Telefone</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_est" name="telefone01_est" value="<?= $telefone01_formatado ?>"
                        placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_est">Celular</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_est" name="telefone02_est" value="<?= $telefone02_formatado ?>"
                        placeholder="(00) 00000-0000">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_contato_est">Nome do Contato</label>
                    <input type="text" class="form-control" id="nome_contato_est" name="nome_contato_est"
                        value="<?= $estipulante->nome_contato_est ?>" placeholder="Nome do contato">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_responsavel_est">Nome do Responsável</label>
                    <input type="text" class="form-control" id="nome_responsavel_est" name="nome_responsavel_est"
                        value="<?= $estipulante->nome_responsavel_est ?>" placeholder="Nome do responsável">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="logo_est">Logo</label>
                    <input type="file" class="form-control" name="logo_est" id="logo_est"
                        accept="image/png, image/jpeg">
                    <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
                </div>
            </div>
            <hr>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Atualizar
                </button>
                <button type="button" class="btn btn-danger" onclick="showConfirmDelete()">
                    Deletar <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true" style="display:none;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar inativação</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Fechar" onclick="hideConfirmDelete()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Este registro será inativado. Deseja continuar?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="hideConfirmDelete()">Não</button>
                        <button type="button" class="btn btn-danger" onclick="confirmAction()">Sim, inativar</button>
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
            form.action = "<?= $BASE_URL ?>process_estipulante.php";

            // Adiciona campos ocultos para o processo de deletar
            const inputType = document.createElement("input");
            inputType.type = "hidden";
            inputType.name = "type";
            inputType.value = "delUpdate";
            form.appendChild(inputType);

            const inputDeleted = document.createElement("input");
            inputDeleted.type = "hidden";
            inputDeleted.name = "deletado_est";
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
function mascara(i, t) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) {
        i.value = v.substring(0, v.length - 1);
        return;
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

const imagem = document.querySelector("#logo_est");
if (imagem) {
    imagem.addEventListener("change", function(e) {
        if (!imagem.files || !imagem.files[0]) return;
        if (imagem.files[0].size > (1024 * 1024 * 2)) {
            var notifImagem = document.querySelector("#notifImagem");
            if (notifImagem) notifImagem.style.display = "block";
            imagem.value = '';
        }
    })
}

function novoArquivo() {
    var notifImagem = document.querySelector("#notifImagem");
    if (notifImagem) notifImagem.style.display = "none";
}
</script>
<?php
include_once("templates/footer.php");
?>
