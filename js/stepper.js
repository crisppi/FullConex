// Validação do Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')

    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})();

function nextStep(step) {
    // Seleciona todos os inputs dentro da etapa atual
    const currentStepForm = document.querySelector(`#step-${step - 1}`);
    const inputs = currentStepForm.querySelectorAll('input, select, textarea');

    let valid = true;
    inputs.forEach((input) => {
        if (!input.checkValidity()) {
            valid = false;
        }
    });

    // Se os inputs não são válidos, não prosseguir e aplicar as classes de validação
    if (!valid) {
        currentStepForm.classList.add('was-validated');
        return;
    }

    // Remover a classe de validação para a próxima etapa
    currentStepForm.classList.remove('was-validated');

    // Mostrar a próxima etapa
    document.querySelectorAll('.step').forEach((stepElement) => {
        stepElement.style.display = 'none';
    });
    document.getElementById('step-' + step).style.display = 'block';

    // Atualizar a barra de progresso
    document.getElementById('progressBar').style.width = (step) * 33.4 + '%';
    document.getElementById('progressBar').innerHTML = `Etapa ${step} de 3`;
}

function prevStep(step) {
    document.querySelectorAll('.step').forEach((stepElement) => {
        stepElement.style.display = 'none';
    });
    document.getElementById('step-' + step).style.display = 'block';

    // Atualizar a barra de progresso
    console.log(step)
    document.getElementById('progressBar').style.width = (step) * 33.4 + '%';
    document.getElementById('progressBar').innerHTML = `Etapa ${step} de 3`;
}

function nextStep2(step) {
    // Seleciona todos os inputs dentro da etapa atual
    const currentStepForm = document.querySelector(`#step-${step - 1}`);
    const inputs = currentStepForm.querySelectorAll('input, select, textarea');

    let valid = true;
    inputs.forEach((input) => {
        if (!input.checkValidity()) {
            valid = false;
        }
    });

    // Se os inputs não são válidos, não prosseguir e aplicar as classes de validação
    if (!valid) {
        currentStepForm.classList.add('was-validated');
        return;
    }

    // Remover a classe de validação para a próxima etapa
    currentStepForm.classList.remove('was-validated');

    // Mostrar a próxima etapa
    document.querySelectorAll('.step').forEach((stepElement) => {
        stepElement.style.display = 'none';
    });
    document.getElementById('step-' + step).style.display = 'block';

    // Atualizar a barra de progresso
    document.getElementById('progressBar').style.width = (step) * 25 + '%';
    document.getElementById('progressBar').innerHTML = `Etapa ${step} de 4`;
}

function prevStep2(step) {
    document.querySelectorAll('.step').forEach((stepElement) => {
        stepElement.style.display = 'none';
    });
    document.getElementById('step-' + step).style.display = 'block';

    // Atualizar a barra de progresso
    console.log(step)
    document.getElementById('progressBar').style.width = (step) * 25 + '%';
    document.getElementById('progressBar').innerHTML = `Etapa ${step} de 4`;
}

function validarCpfExistente(i, t) {
    var v = i.value;
    var formData = new FormData();
    formData.append('cpf', v.replaceAll('.', '').replaceAll("-", ""));
    if (v.length > 0) {
        $.ajax({
            url: 'process_cpf_paciente.php', // URL do arquivo PHP
            type: 'POST', // Método de envio
            processData: false, // Não processar os dados
            contentType: false, // Não definir o tipo de conteúdo
            data: formData, // Dados a serem enviados
            success: function (response) {
                console.log(response);
                if (response == 0) {
                    document.getElementById("validar_cpf").style.display = 'none'
                    document.getElementById("step-1").disabled = false
                } else {
                    document.getElementById("validar_cpf").style.display = 'block'
                    document.getElementById("step-1").disabled = true
                }

            },
            error: function () {
                console.log("error")
            }
        });
    }
}

function validarMatriculaExistente() {
    var v = document.getElementById('matricula_pac').value.trim();
    var recem = document.getElementById('recem_nascido_pac');
    var numeroRNInput = document.getElementById('numero_recem_nascido_pac');
    var isRN = recem && recem.value === 's';

    if (isRN) {
        // Número RN (somente dígitos)
        var numeroRN = (numeroRNInput ? numeroRNInput.value : '').trim().replace(/\D+/g, '');

        // Concatena no formato: MATRICULA + RN + NUMERO
        // Se não tiver número, ainda assim anexa "RN"
        v = v + 'RN' + (numeroRN ? numeroRN : '');
    }

    var formData = new FormData();
    formData.append('matricula', v);

    if (v.length > 0) {
        $.ajax({
            url: 'process_matricula_paciente.php',
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function (response) {
                console.log(response);
                if (response == 0) {
                    document.getElementById("validar_matricula").style.display = 'none';
                    // var elRN = document.getElementById("validar_matricula_rn");
                    // if (elRN) elRN.style.display = 'none';
                    document.getElementById("finalizar_etapa1").disabled = false;
                } else {
                    document.getElementById("validar_matricula").style.display = 'block';
                    // var elRN2 = document.getElementById("validar_matricula_rn");
                    // if (elRN2) elRN2.style.display = 'block';
                    document.getElementById("finalizar_etapa1").disabled = true;
                }
            },
            error: function () {
                console.log("error");
            }
        });
    }
}




function clearAndDisable(input, { required = false } = {}) {
    if (!input) return;
    input.value = '';
    input.required = !!required;
    input.disabled = true;
}

function enableAndRequire(input, { required = false } = {}) {
    if (!input) return;
    input.disabled = false;
    input.required = !!required;
}

function show(el) { if (el) el.style.display = 'block'; }
function hide(el) { if (el) el.style.display = 'none'; }

function handleRecemNascidoChange() {
    const recem = document.getElementById('recem_nascido_pac');

    const maeTitularGroup = document.getElementById('mae_titular_group');
    const maeTitularSelect = document.getElementById('mae_titular_pac');

    const numeroRNGroup = document.getElementById('numero_recem_nascido_group');
    const numeroRNInput = document.getElementById('numero_recem_nascido_pac');

    const matriculaTitularGroup = document.getElementById('matricula_titular_group');
    const matriculaInput = document.getElementById('matricula_titular_pac');

    if (!recem || !maeTitularGroup || !maeTitularSelect || !numeroRNGroup || !numeroRNInput || !matriculaTitularGroup || !matriculaInput) {
        return;
    }

    if (recem.value === 's') {
        // Exibe e habilita campos relativos a RN
        show(maeTitularGroup);
        show(numeroRNGroup);
        enableAndRequire(maeTitularSelect);
        enableAndRequire(numeroRNInput, { required: true });

        // Decide a matrícula da titular conforme escolha da mãe
        handleMaeTitularChange();
    } else {
        // Esconde tudo e limpa/relaxa validações
        hide(maeTitularGroup);
        hide(numeroRNGroup);
        hide(matriculaTitularGroup);

        maeTitularSelect.value = '';
        clearAndDisable(maeTitularSelect);

        clearAndDisable(numeroRNInput);
        clearAndDisable(matriculaInput);
    }
}

function handleMaeTitularChange() {
    const recem = document.getElementById('recem_nascido_pac');

    const maeTitularSelect = document.getElementById('mae_titular_pac');
    const matriculaTitularGroup = document.getElementById('matricula_titular_group');
    const matriculaInput = document.getElementById('matricula_titular_pac');

    if (!recem || !maeTitularSelect || !matriculaTitularGroup || !matriculaInput) return;

    // Só controla matrícula quando for RN
    if (recem.value !== 's') return;

    if (maeTitularSelect.value === 'n') {
        // Mostrar e exigir matrícula da titular
        show(matriculaTitularGroup);
        enableAndRequire(matriculaInput, { required: true });
    } else {
        // Esconder e limpar
        hide(matriculaTitularGroup);
        clearAndDisable(matriculaInput);
    }
}


function handleMaeTitularChange() {

    const recem = document.getElementById('recem_nascido_pac');
    const maeTitularSelect = document.getElementById('mae_titular_pac');
    const matriculaTitularGroup = document.getElementById('matricula_titular_group');
    const matriculaInput = document.getElementById('matricula_titular_pac');



    if (!recem || !maeTitularSelect || !matriculaTitularGroup || !matriculaInput) return;

    if (recem.value !== 's') return;
    console.log("mae change", recem.value, maeTitularSelect.value)
    if (maeTitularSelect.value === 'n') {
        // Mãe NÃO é titular -> pedir matrícula da titular
        matriculaTitularGroup.style.display = 'block';
        matriculaInput.disabled = false;
        matriculaInput.required = true;
    } else {
        // Mãe é titular (ou não selecionado) -> esconder e limpar matrícula
        matriculaTitularGroup.style.display = 'none';
        matriculaInput.value = '';
        matriculaInput.required = false;
        matriculaInput.disabled = true;
    }
}
