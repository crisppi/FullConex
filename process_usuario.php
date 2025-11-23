<?php

require_once("globals.php");
require_once("db.php");
require_once("models/message.php");
require_once("dao/usuarioDao.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário

if ($type === "create") {
    $tipo = ($_FILES['foto_usuario']['type']);
    $tamanho_perm = 1024 * 1024 * 2;
    $size = $_FILES['foto_usuario']['size'];

    $erros = "";

    if (($_FILES['foto_usuario']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {
        // condicao caso arquivo permitido
        $arquivo = ($_FILES['foto_usuario']['name']);
        $temp_arq = ($_FILES['foto_usuario']['tmp_name']);
        $pasta = "uploads/usuarios";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);

        // Resgata dados da imagem
        $tipo = ($_FILES['foto_usuario']['type']);
        $arquivo = ($_FILES['foto_usuario']['name']);
        $temp_arq = ($_FILES['foto_usuario']['tmp_name']);
        $size = ($_FILES['foto_usuario']['size']);
        $pasta = "uploads/usuarios";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);
        // Receber os dados dos inputs
        $usuario_user = filter_input(INPUT_POST, "usuario_user");
        $login_user = filter_input(INPUT_POST, "login_user");
        $fk_usuario_user = filter_input(INPUT_POST, "fk_usuario_user");
        $sexo_user = filter_input(INPUT_POST, "sexo_user");
        $idade_user = filter_input(INPUT_POST, "idade_user");
        $endereco_user = filter_input(INPUT_POST, "endereco_user");
        $numero_user = filter_input(INPUT_POST, "numero_user");
        $cidade_user = filter_input(INPUT_POST, "cidade_user");
        $bairro_user = filter_input(INPUT_POST, "bairro_user");
        $estado_user = filter_input(INPUT_POST, "estado_user");

        $cpf_user = filter_input(INPUT_POST, "cpf_user");
        $cpf_user = str_replace('-', '', $cpf_user);
        $cpf_user = str_replace('.', '', $cpf_user);

        $telefone01_user = filter_input(INPUT_POST, "telefone01_user");
        $telefone01_user = str_replace('-', '', $telefone01_user);
        $telefone01_user = str_replace('(', '', $telefone01_user);
        $telefone01_user = str_replace(') ', '', $telefone01_user);

        $telefone02_user = filter_input(INPUT_POST, "telefone02_user");
        $telefone02_user = str_replace('-', '', $telefone02_user);
        $telefone02_user = str_replace('(', '', $telefone02_user);
        $telefone02_user = str_replace(') ', '', $telefone02_user);
        $telefone02_user = filter_input(INPUT_POST, "telefone02_user");

        $email_user = filter_input(INPUT_POST, "email_user");
        $email_user = strtolower($email_user);

        $email02_user = filter_input(INPUT_POST, "email02_user");
        $email02_user = strtolower($email02_user);

        $ativo_user = filter_input(INPUT_POST, "ativo_user");
        $vinculo_user = filter_input(INPUT_POST, "vinculo_user");
        $depto_user = filter_input(INPUT_POST, "depto_user");
        $nivel_user = filter_input(INPUT_POST, "nivel_user");
        $reg_profissional_user = filter_input(INPUT_POST, "reg_profissional_user");
        $tipo_reg_user = filter_input(INPUT_POST, "tipo_reg_user");
        $depto_user = filter_input(INPUT_POST, "depto_user");

        $usuario_create_user = filter_input(INPUT_POST, "usuario_create_user");
        $data_create_user = filter_input(INPUT_POST, "data_create_user");
        $data_admissao_user = filter_input(INPUT_POST, "data_admissao_user") ?: null;
        $cargo_user = filter_input(INPUT_POST, "cargo_user");
        $obs_user = filter_input(INPUT_POST, "obs_user");
        $senha_default_user = filter_input(INPUT_POST, "senha_default_user");

        // $hash_user = password_hash(filter_input(INPUT_POST, "senha_user"), PASSWORD_DEFAULT);
        // $senha_user = filter_input(INPUT_POST, "senha_user");
        $senha_user = password_hash(filter_input(INPUT_POST, "senha_user"), PASSWORD_DEFAULT);

        $foto_usuarioArray = filter_input(INPUT_POST, "foto_usuario");
        $foto_usuario = $arquivo;
        $usuario = new Usuario();

        // Validação mínima de dados
        if (!empty($usuario_user)) {

            $usuario->usuario_user = $usuario_user;
            $usuario->login_user = $login_user;
            $usuario->fk_usuario_user = $fk_usuario_user;
            $usuario->sexo_user = $sexo_user;
            $usuario->idade_user = $idade_user;

            $usuario->endereco_user = $endereco_user;
            $usuario->numero_user = $numero_user;
            $usuario->bairro_user = $bairro_user;
            $usuario->cidade_user = $cidade_user;
            $usuario->estado_user = $estado_user;

            $usuario->email_user = $email_user;
            $usuario->email02_user = $email02_user;

            $usuario->telefone01_user = $telefone02_user;
            $usuario->telefone02_user = $telefone02_user;
            $usuario->ativo_user = $ativo_user;

            $usuario->reg_profissional_user = $reg_profissional_user;
            $usuario->tipo_reg_user = $tipo_reg_user;

            $usuario->cpf_user = $cpf_user;
            $usuario->senha_user = $senha_user;
            $usuario->senha_default_user = $senha_default_user;

            $usuario->usuario_create_user = $usuario_create_user;
            $usuario->data_admissao_user = $data_admissao_user;
            $usuario->data_create_user = $data_create_user;

            $usuario->vinculo_user = $vinculo_user;
            $usuario->nivel_user = $nivel_user;
            $usuario->depto_user = $depto_user;
            $usuario->cargo_user = $cargo_user;
            $usuario->obs_user = $obs_user;
            $usuario->foto_usuario = $foto_usuario;

            $userDao->create($usuario);
            header("location:list_usuario.php");
        } else {

            //$message->setMessage("Você precisa adicionar pelo menos: nome do useriente!", "error", "back");
        }
    }
} else if ($type === "update") {
    $tipo = ($_FILES['foto_usuario']['type']);
    $tamanho_perm = 1024 * 1024 * 2;
    $size = $_FILES['foto_usuario']['size'];

    $erros = "";

    if (($_FILES['foto_usuario']['size']) > $tamanho_perm) {
        // codigo de erro caso arquivo maior que permitido
    } else {
        // condicao caso arquivo permitido
        $arquivo = ($_FILES['foto_usuario']['name']);
        $temp_arq = ($_FILES['foto_usuario']['tmp_name']);
        $pasta = "uploads/usuarios";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);

        // Resgata dados da imagem
        $tipo = ($_FILES['foto_usuario']['type']);
        $arquivo = ($_FILES['foto_usuario']['name']);
        $temp_arq = ($_FILES['foto_usuario']['tmp_name']);
        $size = ($_FILES['foto_usuario']['size']);
        $pasta = "uploads/usuarios";

        move_uploaded_file($temp_arq, $pasta . "/" . $arquivo);
        $usuarioDao = new userDAO($conn, $BASE_URL);

        // Receber os dados dos inputs
        $id_usuario = filter_input(INPUT_POST, "id_usuario");
        $usuario_user = filter_input(INPUT_POST, "usuario_user");
        $login_user = filter_input(INPUT_POST, "login_user");
        $fk_usuario_user = filter_input(INPUT_POST, "fk_usuario_user");
        $sexo_user = filter_input(INPUT_POST, "sexo_user");
        $idade_user = filter_input(INPUT_POST, "idade_user");

        $cpf_user = filter_input(INPUT_POST, "cpf_user");
        $cpf_user = str_replace('-', '', $cpf_user);
        $cpf_user = str_replace('.', '', $cpf_user);

        $endereco_user = filter_input(INPUT_POST, "endereco_user");
        $numero_user = filter_input(INPUT_POST, "numero_user");
        $cidade_user = filter_input(INPUT_POST, "cidade_user");
        $bairro_user = filter_input(INPUT_POST, "bairro_user");
        $estado_user = filter_input(INPUT_POST, "estado_user") ?: null;

        $email_user = filter_input(INPUT_POST, "email_user");
        $email02_user = filter_input(INPUT_POST, "email02_user");

        $telefone01_user = filter_input(INPUT_POST, "telefone01_user");
        $telefone01_user = str_replace('-', '', $telefone01_user);
        $telefone01_user = str_replace('(', '', $telefone01_user);
        $telefone01_user = str_replace(') ', '', $telefone01_user);

        $telefone02_user = filter_input(INPUT_POST, "telefone02_user");
        $telefone02_user = str_replace('-', '', $telefone02_user);
        $telefone02_user = str_replace('(', '', $telefone02_user);
        $telefone02_user = str_replace(') ', '', $telefone02_user);
        $telefone02_user = filter_input(INPUT_POST, "telefone02_user");

        $ativo_user = filter_input(INPUT_POST, "ativo_user");
        $usuario_create_user = filter_input(INPUT_POST, "usuario_create_user");
        $data_create_user = filter_input(INPUT_POST, "data_create_user");

        $cargo_user = filter_input(INPUT_POST, "cargo_user");
        $depto_user = filter_input(INPUT_POST, "depto_user");
        $vinculo_user = filter_input(INPUT_POST, "vinculo_user");
        $nivel_user = filter_input(INPUT_POST, "nivel_user");

        $hash_user = password_hash(filter_input(INPUT_POST, "senha_user"), PASSWORD_DEFAULT);
        $senha_user = password_hash($hash_user, PASSWORD_DEFAULT);
        $senha_default_user = filter_input(INPUT_POST, "senha_default_user");

        $reg_profissional_user = filter_input(INPUT_POST, "reg_profissional_user");
        $tipo_reg_user = filter_input(INPUT_POST, "tipo_reg_user");

        $data_admissao_user = filter_input(INPUT_POST, "data_admissao_user") ?: null;
        $data_demissao_user = filter_input(INPUT_POST, "data_demissao_user") ?: null;

        $obs_user = filter_input(INPUT_POST, "obs_user");

        $foto_usuarioArray = filter_input(INPUT_POST, "foto_usuario");
        $foto_usuario = $arquivo;

        $usuarioData = $usuarioDao->findById_user($id_usuario);

        $usuarioData->id_usuario = $id_usuario;
        $usuarioData->usuario_user = $usuario_user;
        $usuarioData->login_user = $login_user;
        $usuarioData->fk_usuario_user = $fk_usuario_user;
        $usuarioData->sexo_user = $sexo_user;
        $usuarioData->idade_user = $idade_user;

        $usuarioData->endereco_user = $endereco_user;
        $usuarioData->cidade_user = $cidade_user;
        $usuarioData->numero_user = $numero_user;
        $usuarioData->bairro_user = $bairro_user;
        $usuarioData->estado_user = $estado_user;

        $usuarioData->email_user = $email_user;
        $usuarioData->email02_user = $email02_user;

        $usuarioData->telefone01_user = $telefone01_user;
        $usuarioData->telefone02_user = $telefone02_user;

        $usuarioData->usuario_create_user = $usuario_create_user;
        $usuarioData->data_create_user = $data_create_user;

        $usuarioData->cargo_user = $cargo_user;
        $usuarioData->depto_user = $depto_user;
        $usuarioData->vinculo_user = $vinculo_user;
        $usuarioData->nivel_user = $nivel_user;

        $usuarioData->senha_user = $senha_user;
        $usuarioData->ativo_user = $ativo_user;
        $usuarioData->senha_default_user = $senha_default_user;

        $usuarioData->reg_profissional_user = $reg_profissional_user;
        $usuarioData->tipo_reg_user = $tipo_reg_user;

        $usuarioData->data_admissao_user = $data_admissao_user;
        $usuarioData->data_demissao_user = $data_demissao_user;

        $usuarioData->cpf_user = $cpf_user;
        $usuarioData->obs_user = $obs_user;

        $usuarioData->foto_usuario = $foto_usuario;

        $usuarioDao->update($usuarioData);

        header("location:list_usuario.php");
    }
}
// atualizacao de senha default //
if ($type === "update-senha") {

    $senha_user = filter_input(INPUT_POST, "nova_senha_user");
    $senha_default_user = filter_input(INPUT_POST, "senha_default_user");
    $senha_usuario = filter_input(INPUT_POST, "senha_usuario");
    // $senha_usuario = password_hash(filter_input(INPUT_POST, "senha_usuario"), PASSWORD_DEFAULT);
    // $senha_user = password_hash(filter_input(INPUT_POST, "nova_senha_user"), PASSWORD_DEFAULT);
    $senha_bd = $_SESSION['senha_user'];
    // $senha_bd = password_hash($_SESSION['senha_user'], PASSWORD_DEFAULT);

    if (password_verify($senha_usuario, $senha_bd)) {
        echo 'Password is valid!';
        echo "<hr>";
        // exit;
    } else {
        echo 'Invalid password.';
        echo "<hr>";

        print_r($senha_bd . "<br>");
        print_r($senha_usuario . "<br>");
        // exit;
    }
    ;
    $usuarioDao = new userDAO($conn, $BASE_URL);

    // Receber os dados dos inputs
    $id_usuario = filter_input(INPUT_POST, "id_usuario");

    // $senha_user = filter_input(INPUT_POST, "nova_senha_user");
    $senha_default_user = filter_input(INPUT_POST, "senha_default_user");

    $usuarioData = $usuarioDao->findById_user($id_usuario);

    $usuarioData->id_usuario = $id_usuario;

    $usuarioData->senha_user = password_hash($senha_user, PASSWORD_DEFAULT);
    $usuarioData->senha_default_user = $senha_default_user;

    $usuarioDao->update($usuarioData);

    header("Location: " . $BASE_URL . "dashboard");
    exit;
}
