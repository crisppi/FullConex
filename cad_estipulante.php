<?php
include_once("check_logado.php");

require_once("templates/header.php");
require_once("dao/estipulanteDao.php");
require_once("models/message.php");
include_once("array_dados.php");

$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

// Receber id do usuário
$id_estipulante = filter_input(INPUT_GET, "id_estipulante");

if (empty($id_estipulante)) {

    if (!empty($userData)) {

        $id = $userData->id_estipulante;
    } else {

        //$message->setMessage("Usuário não encontrado!", "error", "index.php");
    }
} else {

    $userData = $userDao->findById($id_estipulante);

    // Se não encontrar usuário
    if (!$userData) {
        $message->setMessage("Usuário não encontrado!", "error", "index.php");
    }
}

?>
<div class="container-fluid" id="main-container">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 33%;" aria-valuenow="33"
            aria-valuemin="0" aria-valuemax="100">Etapa 1 de 3</div>
    </div>
    <form action="<?= $BASE_URL ?>process_estipulante.php" id="multi-step-form" method="POST"
        enctype="multipart/form-data" class="needs-validation" novalidate>

        <input type="hidden" name="type" value="create">
        <input type="hidden" name="deletado_est" value="n">

        <!-- Step 1: Informações Básicas -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="cnpj_est">CNPJ</label>
                    <input type="text" oninput="mascara(this, 'cnpj')" class="form-control" id="cnpj_est"
                        name="cnpj_est" placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_est">Estipulante</label>
                    <input type="text" class="form-control" id="nome_est" name="nome_est"
                        placeholder="Nome do estipulante">
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
                <div class="form-group col-md-6 mb-3">
                    <label for="cep_est">CEP</label>
                    <input type="text" oninput="mascara(this, 'cep')" onkeyup="consultarCEP(this, 'est')"
                        class="form-control" id="cep_est" name="cep_est" placeholder="00000-000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="endereco_est">Endereço</label>
                    <input readonly type="text" class="form-control" id="endereco_est" name="endereco_est"
                        placeholder="Rua, avenida...">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_est">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_est" name="bairro_est"
                        placeholder="Bairro">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_est">Cidade</label>
                    <input readonly type="text" class="form-control" id="cidade_est" name="cidade_est"
                        placeholder="Cidade">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_est">Estado</label>
                    <input readonly class="form-control" id="estado_est" name="estado_est" />

                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_est">Número</label>
                    <input type="text" class="form-control" id="numero_est" name="numero_est" placeholder="Número">
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

        <!-- Step 3: Contato e Finalização -->
        <div id="step-3" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_est">Email Principal</label>
                    <input type="email" class="form-control" id="email01_est" name="email01_est"
                        placeholder="exemplo@dominio.com">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_est">Email Alternativo</label>
                    <input type="email" class="form-control" id="email02_est" name="email02_est"
                        placeholder="exemplo@dominio.com">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_est">Telefone</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_est" name="telefone01_est" placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_est">Celular</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_est" name="telefone02_est" placeholder="(00) 00000-0000">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_contato_est">Nome do Contato</label>
                    <input type="text" class="form-control" id="nome_contato_est" name="nome_contato_est"
                        placeholder="Nome do contato">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="nome_responsavel_est">Nome do Responsável</label>
                    <input type="text" class="form-control" id="nome_responsavel_est" name="nome_responsavel_est"
                        placeholder="Nome do responsável">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="logo_est">Logo</label>
                    <input type="file" class="form-control" onclick="novoArquivo()" name="logo_est" id="logo_est"
                        accept="image/png, image/jpeg">
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


<script>
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

<script>
    // validacao de tamanho do arquivo de imagem
    const imagem = document.querySelector("#logo_est")
    // console.log(imagem);

    imagem.addEventListener("change", function (e) {
        console.log(imagem.files[0].size);
        if (imagem.files[0].size > (1024 * 1024 * 2)) {

            // Apresentar a mensagem de erro
            // alert("Tamanho máximo permitido do arquivo é 2mb.");
            var notifImagem = document.querySelector("#notifImagem");
            notifImagem.style.display = "block";

            // Limpar o campo arquivo
            imagem.value = '';
            //(imagem ? imagem.value = '' : null)
        }
    })

    function novoArquivo() {
        notifImagem.style.display = "none";

    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<?php
require_once("templates/footer.php");
?>