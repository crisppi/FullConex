<?php
include_once("check_logado.php");

require_once("dao/hospitalDao.php");
require_once("models/seguradora.php");
require_once("dao/seguradoraDao.php");
require_once("models/estipulante.php");
require_once("dao/estipulanteDao.php");
require_once("models/message.php");

$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);
$seguradoras = $seguradoraDao->findAll();

$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);
$estipulantes = $estipulanteDao->findAll();

// Receber id do usuário
$id_hospital = filter_input(INPUT_GET, "id_hospital");
?>

<!-- Incluindo o Font Awesome para os ícones -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="container-fluid" id="main-container">
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" id="progressBar" style="width: 100%;"
            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">Dados do Paciente</div>
    </div>
    <form action="<?= $BASE_URL ?>process_paciente.php" id="multi-step-form" method="POST" enctype="multipart/form-data"
        class="needs-validation">

        <input type="hidden" name="type" value="create">
        <input type="hidden" name="deletado_pac" value="n">

        <!-- Step 1: Personal Information -->
        <div id="step-1" class="step">
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="cpf_pac">CPF</label>
                    <input class="form-control" type="text" oninput="mascara(this, 'cpf')" id="cpf_pac" name="cpf_pac"
                        placeholder="000.000.000-00">
                    <div class="invalid-feedback">
                        Por favor, insira um CPF válido.
                    </div>
                    <div class="invalid-feedback" id="validar_cpf" style="display: none;">
                        CPF já cadastrado.
                    </div>
                </div>
                <div class="form-group col-md-8 mb-3">
                    <label for="nome_pac">Nome</label>
                    <input type="text" class="form-control" id="nome_pac" name="nome_pac" required>
                    <div class="invalid-feedback">
                        Por favor, insira o nome.
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="recem_nascido_pac">Recém-nascido?</label>
                    <select class="form-control" id="recem_nascido_pac" name="recem_nascido_pac"
                        onchange="handleRecemNascidoChange()">
                        <option value="">Selecione</option>
                        <option value="s">Sim</option>
                        <option value="n" selected>Não</option>
                    </select>
                </div>
                <!-- <div class="invalid-feedback" id="validar_matricula_rn" style="display: none;">
                    Matrícula já cadastrada para RN.
                </div> -->

                <!-- Número RN -->
                <div class="form-group col-md-2 mb-3" id="numero_recem_nascido_group" style="display:none;">
                    <label for="numero_recem_nascido_pac">Número RN</label>
                    <input type="text" class="form-control" id="numero_recem_nascido_pac"
                        onkeyup="validarMatriculaExistente()" name="numero_recem_nascido_pac" placeholder="Ex: 1, 2..."
                        disabled>
                    <div class="invalid-feedback">Informe apenas números.</div>
                </div>

                <!-- Select: Mãe Titular -->
                <div class="form-group col-md-2 mb-3" id="mae_titular_group" style="display: none;">
                    <label for="mae_titular_pac">Mãe Titular?</label>
                    <select class="form-control" id="mae_titular_pac" name="mae_titular_pac"
                        onchange="handleMaeTitularChange()">
                        <option value="s" selected>Sim</option>
                        <option value="n">Não</option>
                    </select>
                </div>




                <!-- Input: Matrícula da Titular -->
                <div class="form-group col-md-4 mb-3" id="matricula_titular_group" style="display: none;">
                    <label for="matricula_titular_pac">Matrícula do Titular</label>
                    <input type="text" class="form-control" id="matricula_titular_pac" name="matricula_titular_pac">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 mb-3">
                    <label for="data_nasc_pac">Nascimento</label>
                    <input type="date" class="form-control" id="data_nasc_pac" name="data_nasc_pac">
                    <div class="invalid-feedback">
                        Por favor, insira a data de nascimento.
                    </div>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="matricula_pac">Matrícula</label>
                    <input type="text" class="form-control" onkeyup="validarMatriculaExistente()" id="matricula_pac"
                        name="matricula_pac" required>
                    <div class="invalid-feedback">
                        Por favor, insira a matrícula.
                    </div>
                    <div class="invalid-feedback" id="validar_matricula" style="display: none;">
                        Matrícula já cadastrada.
                    </div>

                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="sexo_pac">Sexo</label>
                    <select class="form-control" name="sexo_pac" id="sexo_pac">
                        <option value="" selected disabled>Selecione...</option>
                        <option value="f">Feminino</option>
                        <option value="m">Masculino</option>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione o sexo.</div>
                </div>

            </div>
            <!-- <div class="form-group col-md-8 mb-3">
                    <label for="mae_pac">Mãe</label>
                    <input type="text" class="form-control" id="mae_pac" name="mae_pac">
                </div> -->

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="fk_seguradora_pac">Seguradora</label>
                    <select class="form-control" id="fk_seguradora_pac" name="fk_seguradora_pac">
                        <option value="1">Selecione</option>
                        <?php foreach ($seguradoras as $seguradora): ?>
                        <option value="<?= $seguradora["id_seguradora"] ?>"><?= $seguradora['seguradora_seg'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="fk_estipulante_pac">Estipulante</label>
                    <select class="form-control" id="fk_estipulante_pac" name="fk_estipulante_pac">
                        <option value="1">Selecione</option>
                        <?php foreach ($estipulantes as $estipulante): ?>
                        <option value="<?= $estipulante["id_estipulante"] ?>"><?= $estipulante['nome_est'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- <div class="form-group col-md-6 mb-3">
                    <label for="matricula_pac">Matrícula</label>
                    <input type="text" class="form-control" id="matricula_pac" name="matricula_pac">
                </div> -->
            </div>
            <div class="form-group mb-3">
                <label for="obs_pac">Observações</label>
                <textarea rows="5" class="form-control" id="obs_pac" name="obs_pac"></textarea>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <!-- <button type="button" class="btn btn-primary" id="next-1" onclick="nextStep(2)">
                    Próximo <i class="fas fa-arrow-right"></i>
                </button> -->
                <button type="submit" class="btn btn-success" name="finalizar_etapa1" id="finalizar_etapa1">
                    <i class="fas fa-check"></i> Finalizar Cadastro
                </button>
            </div>


        </div>

        <!-- Step 2: Address Information -->
        <div id="step-2" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <label for="cep_pac">CEP</label>
                    <input type="text" oninput="mascara(this, 'cep')" onkeyup="consultarCEP(this, 'pac')"
                        class="form-control" id="cep_pac" name="cep_pac" placeholder="00000-000">
                    <div class="invalid-feedback">
                        Por favor, insira o CEP.
                    </div>
                </div>
                <div class="form-group col-md-9 mb-3">
                    <label for="endereco_pac">Endereço</label>
                    <input readonly type="text" class="form-control" id="endereco_pac" name="endereco_pac"
                        placeholder="...">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="bairro_pac">Bairro</label>
                    <input readonly type="text" class="form-control" id="bairro_pac" name="bairro_pac"
                        placeholder="...">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="cidade_pac">Cidade</label>
                    <input readonly type="text" class="form-control" id="cidade_pac" name="cidade_pac"
                        placeholder="...">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="estado_pac">Estado</label>
                    <select readonly class="form-control" id="estado_pac" name="estado_pac">
                        <option value="">...</option>
                        <?php foreach ($estado_sel as $estado): ?>
                        <option value="<?= $estado ?>"><?= $estado ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="numero_pac">Número</label>
                    <input type="text" class="form-control" id="numero_pac" name="numero_pac">
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="complemento_pac">Complemento</label>
                <input type="text" class="form-control" id="complemento_pac" name="complemento_pac">
            </div>
            <hr>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                Próximo <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Step 3: Contact & Other Information -->
        <div id="step-3" class="step" style="display:none;">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="email01_pac">Email Principal</label>
                    <input type="email" class="form-control" id="email01_pac" name="email01_pac"
                        placeholder="exemplo@dominio.com">
                    <div class="invalid-feedback">
                        Por favor, insira um email válido.
                    </div>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="email02_pac">Email Alternativo</label>
                    <input type="email" class="form-control" id="email02_pac" name="email02_pac"
                        placeholder="exemplo@dominio.com">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone01_pac">Telefone</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone01_pac" name="telefone01_pac" placeholder="(00) 0000-0000">
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="telefone02_pac">Celular</label>
                    <input type="text" onkeydown="return mascaraTelefone(event)" class="form-control"
                        id="telefone02_pac" name="telefone02_pac" placeholder="(00) 00000-0000">
                    <div class="invalid-feedback">
                        Por favor, insira um número de celular válido.
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="css/style.css">

<?php
require_once("templates/footer.php");
?>
