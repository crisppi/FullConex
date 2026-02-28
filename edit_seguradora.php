<?php

include_once("check_logado.php");

require_once("models/usuario.php");
require_once("models/seguradora.php");
require_once("dao/usuarioDao.php");
require_once("dao/seguradoraDao.php");
require_once("templates/header.php");
require_once("array_dados.php");

$user = new seguradora();
$userDao = new UserDAO($conn, $BASE_URL);
$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);

// Receber id do usuário
$id_seguradora = filter_input(INPUT_GET, "id_seguradora");

$seguradora = $seguradoraDao->findById($id_seguradora);
$estado_selecionado = $seguradora->estado_seg;

$cep_formatado = formatarCEP($seguradora->cep_seg);
$cnpj_formatado = formatarCNPJ($seguradora->cnpj_seg);
$telefone01_formatado = formatarTelefone($seguradora->telefone01_seg);
$telefone02_formatado = formatarTelefone($seguradora->telefone02_seg);
$valor_alto_custo_seg = str_replace(',', '.', $seguradora->valor_alto_custo_seg);
$valor_alto_custo_formatado = number_format(floatval($valor_alto_custo_seg), 2, ',', '.');

function formatarCEP($cep)
{
    if (!empty($cep)) {
        $cep = preg_replace("/\D/", '', $cep);
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }
    }
    return '';
}

function formatarCNPJ($cnpj)
{
    if (!empty($cnpj)) {
        $cnpj = preg_replace("/\D/", '', $cnpj);
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        }
    }
    return '';
}

function formatarTelefone($telefone)
{
    if (!empty($telefone)) {
        $telefone = preg_replace("/\D/", '', $telefone);
        if (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
        } elseif (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
        }
    }
    return '';
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

<div id="main-container" class="internacao-page">
    <div class="internacao-page__hero">
        <div><h1>Editar seguradora</h1></div>
        <div class="hero-actions">
            <a class="hero-back-btn" href="<?= htmlspecialchars(rtrim($BASE_URL, '/') . '/seguradoras', ENT_QUOTES, 'UTF-8') ?>">Voltar para lista</a>
            <span class="internacao-page__tag">Campos obrigatórios em destaque</span>
        </div>
    </div>
    <div class="internacao-page__content">
        <form class="container-fluid fundo_tela_cadastros" action="<?= $BASE_URL ?>process_seguradora.php"
            id="multi-step-form" method="POST" enctype="multipart/form-data">
            <div class="internacao-card internacao-card--general">
                <div class="internacao-card__header">
                    <div><p class="internacao-card__eyebrow">Dados da seguradora</p></div>
                </div>
                <div class="internacao-card__body">
            <input type="hidden" name="type" value="update">
            <input type="hidden" class="form-control" id="id_seguradora" name="id_seguradora"
                value="<?= $seguradora->id_seguradora ?>">
            <input type="hidden" name="deletado_seg" value="n">
            <!-- Step 1: Informações da Seguradora -->
            <div id="step-1" class="step">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="seguradora_seg"><span style="color:red;">*</span> Seguradora</label>
                        <input type="text" class="form-control" id="seguradora_seg" name="seguradora_seg"
                            value="<?= $seguradora->seguradora_seg ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cnpj_seg">CNPJ</label>
                        <input type="text" class="form-control" id="cnpj_seg" name="cnpj_seg"
                            oninput="mascara(this, 'cnpj')" value="<?= $cnpj_formatado ?>"
                            placeholder="00.000.000/0000-00">
                    </div>
                </div>
                <hr>
            </div>

            <!-- Step 2: Endereço -->
            <div id="step-2" class="step">
                <p class="internacao-card__eyebrow mb-3">Dados de endereço</p>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="cep_seg">CEP</label>
                        <input type="text" class="form-control" id="cep_pac" name="cep_seg"
                            onkeyup="consultarCEP(this, 'seg')" value="<?= $cep_formatado ?>" placeholder="00000-000">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="endereco_seg">Endereço</label>
                        <input readonly type="text" class="form-control" id="endereco_seg" name="endereco_seg"
                            value="<?= $seguradora->endereco_seg ?>" placeholder="Rua, Avenida, etc.">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="bairro_seg">Bairro</label>
                        <input readonly type="text" class="form-control" id="bairro_seg" name="bairro_seg"
                            value="<?= $seguradora->bairro_seg ?>" placeholder="Digite o bairro">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cidade_seg">Cidade</label>
                        <input readonly type="text" class="form-control" id="cidade_seg" name="cidade_seg"
                            value="<?= $seguradora->cidade_seg ?>" placeholder="Digite a cidade">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="estado_seg">Estado</label>
                        <select readonly class="form-control" id="estado_seg" name="estado_seg" required>
                            <option value="">Selecione o estado</option>
                            <?php foreach ($estado_sel as $estado): ?>
                            <option value="<?= $estado ?>" <?= $estado_selecionado == $estado ? 'selected' : '' ?>>
                                <?= $estado ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numero_seg">Número</label>
                        <input type="number" class="form-control" id="numero_seg" name="numero_seg"
                            value="<?= $seguradora->numero_seg ?>" placeholder="Número do endereço">
                    </div>
                </div>
                <hr>
            </div>

            <!-- Step 3: Contato e Informações Complementares -->
            <div id="step-3" class="step">
                <p class="internacao-card__eyebrow mb-3">Dados de contato</p>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="email01_seg">Email Principal</label>
                        <input type="email" class="form-control" id="email01_seg" name="email01_seg"
                            value="<?= $seguradora->email01_seg ?>" placeholder="exemplo@dominio.com">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email02_seg">Email Alternativo</label>
                        <input type="email" class="form-control" id="email02_seg" name="email02_seg"
                            value="<?= $seguradora->email02_seg ?>" placeholder="exemplo@dominio.com">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="telefone01_seg">Telefone</label>
                        <input type="text" class="form-control" id="telefone01_seg" name="telefone01_seg"
                            onkeydown="return mascaraTelefone(event)" value="<?= $telefone01_formatado ?>"
                            placeholder="(00) 0000-0000">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="telefone02_seg">Telefone Alternativo</label>
                        <input type="text" class="form-control" id="telefone02_seg" name="telefone02_seg"
                            onkeydown="return mascaraTelefone(event)" value="<?= $telefone02_formatado ?>"
                            placeholder="(00) 0000-0000">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="ativo_seg">Ativo</label>
                        <select class="form-control" id="ativo_seg" name="ativo_seg">
                            <option value="s" <?= $seguradora->ativo_seg == 's' ? 'selected' : '' ?>>Sim</option>
                            <option value="n" <?= $seguradora->ativo_seg == 'n' ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="coord_rh_seg">Coordenador RH</label>
                        <input type="text" class="form-control" id="coord_rh_seg" name="coord_rh_seg"
                            value="<?= $seguradora->coord_rh_seg ?>" placeholder="Nome do Coordenador RH">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="coordenador_seg">Coordenador</label>
                        <input type="text" class="form-control" id="coordenador_seg" name="coordenador_seg"
                            value="<?= $seguradora->coordenador_seg ?>" placeholder="Nome do Coordenador">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contato_seg">Contato Seguradora</label>
                        <input type="text" class="form-control" id="contato_seg" name="contato_seg"
                            value="<?= $seguradora->contato_seg ?>" placeholder="Nome do contato na seguradora">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="dias_visita_seg">Dias Visita Clínica</label>
                        <input type="text" class="form-control" id="dias_visita_seg" name="dias_visita_seg"
                            value="<?= $seguradora->dias_visita_seg ?>"
                            placeholder="Digite os dias de visita à clínica">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="dias_visita_uti_seg">Dias Visita UTI</label>
                        <input type="text" class="form-control" id="dias_visita_uti_seg" name="dias_visita_uti_seg"
                            value="<?= $seguradora->dias_visita_uti_seg ?>"
                            placeholder="Digite os dias de visita à UTI">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="valor_alto_custo_seg">Valor Alto Custo</label>
                        <input type="text" class="form-control" id="valor_alto_custo_seg" name="valor_alto_custo_seg"
                            value="<?= $valor_alto_custo_formatado ?>" placeholder="Valor alto custo">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="longa_permanencia_seg">Longa Permanência</label>
                        <input type="text" class="form-control" id="longa_permanencia_seg" name="longa_permanencia_seg"
                            value="<?= $seguradora->longa_permanencia_seg ?>" placeholder="Longa permanência">
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="logo_seg">Logo</label>
                        <input type="file" class="form-control" name="logo_seg" id="logo_seg"
                            accept="image/png, image/jpeg">
                        <div class="notif-input oculto" id="notifImagem">Tamanho do arquivo inválido!</div>
                    </div>
                    <?php if (!empty($seguradora->logo_seg)): ?>
                    <div class="form-group col-md-6">
                        <label>Logo Atual</label>
                        <img src="uploads/<?= $seguradora->logo_seg; ?>" height="80" width="80">
                    </div>
                    <?php endif; ?>
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
                form.action = "<?= $BASE_URL ?>process_seguradora.php";

                // Adiciona campos ocultos para o processo de deletar
                const inputType = document.createElement("input");
                inputType.type = "hidden";
                inputType.name = "type";
                inputType.value = "delUpdate";
                form.appendChild(inputType);

                const inputDeleted = document.createElement("input");
                inputDeleted.type = "hidden";
                inputDeleted.name = "deletado_seg";
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
</script>

<script>
const imagem = document.querySelector("#logo_seg");

if (imagem) {
    imagem.addEventListener("change", function(e) {
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
<?php require_once("templates/footer.php"); ?>
