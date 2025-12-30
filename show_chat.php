<?php
include_once("check_logado.php");
include_once("globals.php");
include_once("models/mensagem.php");
require_once("dao/mensagemDao.php");
require_once("dao/usuarioDao.php");
require_once("app/services/AssistenteVirtualService.php");

$de_usuario = $_SESSION['id_usuario'];
$para_usuario = isset($_GET['para_usuario']) ? (int) $_GET['para_usuario'] : null;

$assistantService = new AssistenteVirtualService($conn, $BASE_URL);
$assistantId = $assistantService->getAssistantUserId();
$assistantSummary = $assistantService->getAssistantSummary();

if ($para_usuario === null) {
    $para_usuario = $assistantId;
}

$mensagemDao = new mensagemDAO($conn, $BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$assistantUserProfile = $userDao->findById_user($assistantId);
$users = $userDao->findAllMensagens($de_usuario);

$assistantInList = false;
foreach ($users as $user) {
    if ((int) $user['id_usuario'] === $assistantId) {
        $assistantInList = true;
        break;
    }
}

if (!$assistantInList) {
    $users = array_merge([[
        'id_usuario' => $assistantId,
        'usuario_user' => $assistantUserProfile ? $assistantUserProfile->usuario_user : $assistantSummary['titulo'],
        'foto_usuario' => $assistantUserProfile ? $assistantUserProfile->foto_usuario : 'default-user.jpeg',
        'ultima_mensagem' => $assistantSummary['descricao'],
        'data_mensagem' => null,
        'vista' => 1,
        'para_usuario' => $de_usuario
    ]], $users);
}

$user_para = $userDao->findById_user($para_usuario);
if (!$user_para && $assistantService->isAssistantUser($para_usuario)) {
    $user_para = $assistantUserProfile;
}

$mensagemDao->marcarMensagensComoLidas($de_usuario, $para_usuario);

include_once("templates/header.php");
?>

<!DOCTYPE html>
<html lang="pt-br">
<script src="js/timeout.js"></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/chat_styles.css">
</head>

<body>
    <div class="container-fluid chat-page">
        <div class="row d-flex align-items-stretch chat-row">
            <div class="col-3 sidebar">
                <ul class="user-list">
                    <?php foreach ($users as $user): ?>
                    <li class="d-flex align-items-start w-100 py-2 border-bottom">
                        <a href="show_chat.php?para_usuario=<?= urlencode($user['id_usuario']) ?>"
                            class="d-flex align-items-start w-100 text-decoration-none">
                            <img src="<?= 'uploads/usuarios/' . $user['foto_usuario'] ?>"
                                alt="Foto de <?= htmlspecialchars($user['usuario_user']) ?>" class="user-photo">
                            <div class="d-flex flex-column w-100 ms-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <?= htmlspecialchars($user['usuario_user']) ?>
                                        <?php if ($user['vista'] == 0 && $user['para_usuario'] == $de_usuario): ?>
                                        <span class="badge bg-danger ms-2">Nova</span>
                                        <?php endif; ?>
                                    </span>
                                    <small
                                        class="text-muted"><?= $user['data_mensagem'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($user['data_mensagem']))) : "Iniciar Conversa" ?></small>
                                </div>
                                <p class="mb-0 text-truncate text-muted small">
                                    <?= htmlspecialchars($user['ultima_mensagem']) ?>
                                </p>
                            </div>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-9 chat-container">
                <?php if ($user_para): ?>
                <div class="chat-header d-flex align-items-center">
                    <img src="<?= 'uploads/usuarios/' . $user_para->foto_usuario ?>"
                        alt="Foto de <?= htmlspecialchars($user_para->usuario_user) ?>" class="user-photo-chat">
                    <h3 class="ms-3"><?= htmlspecialchars($user_para->usuario_user) ?></h3>
                </div>
                <?php else: ?>
                <div class="chat-header d-flex align-items-center">
                    <h3 class="ms-3">Selecione um usuário para iniciar a conversa</h3>
                </div>
                <?php endif; ?>

                <?php if ($assistantService->isAssistantUser($para_usuario)): ?>
                <div class="alert alert-info py-2">
                    <strong><?= htmlspecialchars($assistantSummary['titulo']) ?>:</strong>
                    <?= htmlspecialchars($assistantSummary['descricao']) ?>
                    <br><small>As respostas são automáticas e precisam de revisão humana antes de seguir para o
                        cliente.</small>
                </div>
                <?php endif; ?>

                <div class="chat-body">
                    <div id="message-box" class="message-box flex-grow-1">
                        <!-- Messages will be dynamically loaded here via AJAX -->
                    </div>
                </div>

                <div class="chat-input">
                    <form id="chat-form">
                        <div class="input-group">
                            <input type="hidden" id="chat-target" value="<?= (int) $para_usuario ?>">
                            <input type="text" id="message" class="form-control" placeholder="Digite sua mensagem..."
                                required autocomplete="off">
                            <button type="button" class="btn btn-secondary" id="add-capeante-link"
                                title="Adicionar referência de capeante">+</button>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Incluindo JQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Variável de controle para saber se é a primeira vez que o chat está sendo carregado
    var isFirstLoad = true;
    var chatTarget = parseInt($('#chat-target').val(), 10) || <?= (int) $assistantId ?>;

    function loadMessages() {
        var ultimo_id = $('#message-box .message').last().data('id') || 0;

        $.ajax({
            url: 'load_messages.php',
            method: 'GET',
            data: {
                de_usuario: <?= (int) $de_usuario ?>,
                para_usuario: chatTarget,
                ultima_msg: ultimo_id
            },
            success: function(data) {
                if (data.trim() !== '') {
                    var $newMessages = $(data);

                    // Create a document fragment to append new messages to
                    var docFragment = document.createDocumentFragment();
                    var hasNewMessages = false; // Flag to track if new messages are added

                    // Verificar se há mensagens novas
                    $newMessages.each(function() {
                        var messageId = $(this).data('id');

                        // Adicionar apenas se a mensagem ainda não estiver no DOM
                        if ($('#message-box .message[data-id="' + messageId + '"]').length === 0) {
                            // Convert jQuery object to DOM element and append to fragment
                            docFragment.appendChild($(this)[0]);
                            hasNewMessages = true; // New message found
                        }
                    });

                    // Append the fragment with all new messages at once to the message box
                    if (hasNewMessages) {
                        $('#message-box')[0].appendChild(docFragment);
                    }

                    // Scroll only if new messages were added or it's the first load
                    if (hasNewMessages || isFirstLoad) {
                        $('#message-box').scrollTop($('#message-box')[0].scrollHeight);
                        isFirstLoad = false; // Definir como falso após o primeiro carregamento
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log("Erro ao carregar mensagens: " + error);
            }
        });
    }


    loadMessages();
    setInterval(loadMessages, 2000);
    // Envio de mensagem via Ajax
    // Envio de mensagem via Ajax
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        var message = $('#message').val();
        if (message.trim() === '') return;

        $.ajax({
            url: 'cad_mensagem.php',
            method: 'POST',
            data: {
                de_usuario: <?= (int) $de_usuario ?>,
                para_usuario: chatTarget,
                mensagem: message
            },
            success: function() {
                $('#message').val(''); // Limpa o campo de mensagem
                loadMessages(); // Carrega as mensagens novamente

                // Scroll automático para o final após o envio da mensagem
                $('#message-box').scrollTop($('#message-box')[0].scrollHeight);
            }
        });
    });

    $(document).ready(function() {
        // Função para inserir o link capeante no campo de mensagem
        $('#add-capeante-link').on('click', function() {
            var idCapeante = prompt("Por favor, insira o ID do capeante:");

            if (idCapeante) {
                var messageField = $('#message');
                var currentText = messageField.val();

                // Insere o texto 'link_capeante={id}' no campo de mensagem
                var capeanteLink = ' link_capeante=' + idCapeante + ' ';

                // Adiciona o texto na posição atual do cursor
                insertAtCursor(messageField[0], capeanteLink);
            }
        });

        // Função para inserir o texto na posição do cursor
        function insertAtCursor(field, text) {
            // Verifica se o navegador suporta o método de inserção de texto no cursor
            if (document.selection) {
                // Para navegadores mais antigos (IE)
                field.focus();
                var sel = document.selection.createRange();
                sel.text = text;
            } else if (field.selectionStart || field.selectionStart === 0) {
                // Para navegadores modernos (Chrome, Firefox, etc.)
                var startPos = field.selectionStart;
                var endPos = field.selectionEnd;
                field.value = field.value.substring(0, startPos) +
                    text +
                    field.value.substring(endPos, field.value.length);
            } else {
                // Caso o campo não suporte a posição do cursor, apenas adiciona no final
                field.value += text;
            }
        }
    });
    </script>
    <?php
    require_once("templates/footer.php");
    ?>

    <!-- Inclui o CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Inclui o JavaScript do Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
</body>

</html>
