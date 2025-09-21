// function checkSenha() {
//     let senhaUsuarioBd = document.getElementById("senha_usuario"); //senha do banco de dados
//     let senhaUsuarioLogin = document.getElementById("senha_user"); //senha digitada no formulario

//     var divMsgErr = document.querySelector("#notif-erro");

//     if (senhaUsuarioBd.value === senhaUsuarioLogin.value) {
//         //console.log("senha igual")

//     } else {
//         divMsgErr.style.display = "block";
//         senhaUsuarioLogin.value = "";
//         senhaUsuarioLogin.focus();
//         //console.log("senha diferente")
//     }
// }

function check() {
    let novaSenha = document.getElementById("nova_senha_user");
    let novaSenha2 = document.getElementById("nova_senha_user2");

    var divMsg = document.querySelector("#notif-input");

    if (novaSenha2.value === novaSenha.value) {
        //console.log("senha igual")

    } else {
        //console.log("senha diferente")
        novaSenha.value = "";
        novaSenha2.value = "";
        novaSenha.focus();

        divMsg.style.display = "block";
    }
}

function checkIn() {
    let novaSenha = document.getElementById("nova_senha_user");
    let novaSenha2 = document.getElementById("nova_senha_user2");

    var divMsg = document.querySelector("#notif-input");
    divMsg.style.display = "none";
}

function checkInSenha() {
    let senhaUsuarioBd = document.getElementById("senha_usuario");
    let senhaUsuarioLogin = document.getElementById("senha_user");

    var divMsgErr = document.querySelector("#notif-erro");
    divMsgErr.style.display = "none";
}