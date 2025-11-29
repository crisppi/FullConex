<?php
require_once __DIR__ . '/dao/permissionDao.php';

final class Gate
{
    /** Detecta a ação: create | edit | delete */
    private static function detectAction(string $script): ?string
    {
        $f = strtolower(basename($script));

        // 1) DELETE por nome
        if (preg_match('/(excluir|deletar|remover|delete|destroy)/', $f)) return 'delete';

        // 2) EDIT se vier ID > 0 no POST (ajuste as chaves conforme seus forms)
        foreach (array_keys($_POST) as $k) {
            $lk = strtolower($k);
            if (preg_match('/^(id(_|)internac|cod_internac|id_paciente|id_atendimento|id_registro|id)$/', $lk)) {
                if ((int)($_POST[$k] ?? 0) > 0) return 'edit';
            }
        }

        // 3) EDIT por nome do arquivo
        if (preg_match('/(editar|atualizar|update|edit)/', $f)) return 'edit';

        // 4) CREATE por nome do arquivo
        if (preg_match('/(criar|novo|salvar|create|store|insert|cad|gravar|save|process)/', $f)) return 'create';

        return null;
    }

    public static function autoEnforce(PDO $conn, string $BASE_URL): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) return; // nunca barra GET

        $script = strtolower($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptBase = strtolower(basename($script));
        if (($scriptBase === 'process_usuario.php' && strtolower($_POST['type'] ?? '') === 'update-senha')
            || $scriptBase === 'process_reset_senha.php' || $scriptBase === 'nova_senha.php') {
            return;
        }

        // ignora assets
        $script = strtolower($_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|map)$/', $script)) return;

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $idUser = (int)($_SESSION['id_usuario'] ?? 0);
        $cargo  = (string)($_SESSION['cargo'] ?? '');
        $nivel  = (string)($_SESSION['nivel'] ?? '');
        $ativo  = strtolower((string)($_SESSION['ativo'] ?? ''));

        // diretoria/admin tem passe livre
        $norm = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $c = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            $s = $c !== false ? $c : $s;
            return preg_replace('/[^a-z]/', '', $s);
        };
        $isDiretoria = in_array($norm($cargo), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
            || in_array($norm($nivel), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
            || ((int)$nivel === -1);

        if (!$idUser || $ativo !== 's') {
            self::flashAndGo($BASE_URL, 'Não autenticado.', self::guessListUrl($BASE_URL, $script, $_SERVER['HTTP_REFERER'] ?? ''));
        }
        if ($isDiretoria) return;

        $action = self::detectAction($_SERVER['SCRIPT_NAME'] ?? '');
        if ($action === null) return; // não deu pra inferir => não bloqueia

        $permDao = new PermissionDAO($conn, $BASE_URL);
        if (!$permDao->userCan($idUser, $action)) {
            $v = ['create' => 'criar', 'edit' => 'editar', 'delete' => 'excluir'][$action] ?? $action;
            $to = self::guessListUrl($BASE_URL, $script, $_SERVER['HTTP_REFERER'] ?? '');
            self::flashAndGo($BASE_URL, "Você não tem permissão para $v este recurso.", $to);
        }
    }

    /** Salva flash e redireciona (ou JSON se AJAX) */
    private static function flashAndGo(string $BASE_URL, string $message, string $to): void
    {
        // AJAX? devolve JSON e não redireciona
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || str_contains($accept, 'application/json')
            || str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['mensagem']      = $message;
        $_SESSION['mensagem_tipo'] = 'danger'; // vermelho

        // redireciona direto para a lista (sem ecoar nada)
        $target = $to ?: ($BASE_URL . 'dashboard');
        header('Location: ' . $target, true, 303); // 303 evita re-post
        exit;
    }

    /** Decide a "lista" adequada com base no script ou referer */
    private static function guessListUrl(string $BASE_URL, string $script, string $referer): string
    {
        // 1) Mapa manual (AJUSTE AQUI para seus nomes reais)
        $manualMap = [
            'internac' => 'internacoes/lista', // <— exemplo real seu; ajuste se for outro
            'pacient'  => 'lista_pacientes.php',
            'usuario'  => 'list_usuario.php',
            'tuss'     => 'lista_tuss.php',
            'visita'   => 'lista_visitas.php',
        ];

        $toPath = null;
        $script = strtolower($script);
        $refUrl = parse_url($referer ?: '', PHP_URL_PATH) ?: '';
        $refBase = $refUrl ? basename($refUrl) : '';
        $appBasePath = rtrim(dirname(parse_url($BASE_URL, PHP_URL_PATH) ?? '/'), '/'); // /FullConex
        if ($appBasePath === '') $appBasePath = '/';

        // 2) Se bater no mapa manual, use-o
        foreach ($manualMap as $needle => $file) {
            if (str_contains($script, $needle) || ($refBase && str_contains($refBase, $needle))) {
                $toPath = $file;
                break;
            }
        }

        // 3) Se não achou no mapa, derive candidatos pelo nome do referer
        if (!$toPath && $refBase) {
            // edit_internacao.php -> internacao
            $name = preg_replace('/^(edit|editar|form|process|proc|cad|save|salvar|novo|new|update|atualizar)_/i', '', $refBase);
            $name = preg_replace('/\.(php|html)$/i', '', $name); // tira extensão
            $candidates = [
                "lista_{$name}.php",
                "list_{$name}.php",
                "listar_{$name}.php",
            ];
            foreach ($candidates as $cand) {
                if (self::fileExistsInApp($appBasePath, $cand)) {
                    $toPath = $cand;
                    break;
                }
            }
        }

        // 4) Se ainda não achou, tente candidatos genéricos pelo script
        if (!$toPath) {
            $name = strtolower(basename($script));
            $name = preg_replace('/^(edit|editar|form|process|proc|cad|save|salvar|novo|new|update|atualizar)_/i', '', $name);
            $name = preg_replace('/\.(php|html)$/i', '', $name);
            $candidates = [
                "lista_{$name}.php",
                "list_{$name}.php",
                "listar_{$name}.php",
            ];
            foreach ($candidates as $cand) {
                if (self::fileExistsInApp($appBasePath, $cand)) {
                    $toPath = $cand;
                    break;
                }
            }
        }

        // 5) Fallback: se o referer já era uma lista válida, volta pra ele
        if (!$toPath && $refBase && preg_match('/^lista?r?_.*\.php$/', $refBase)) {
            if (self::fileExistsInApp($appBasePath, $refBase)) {
                $toPath = $refBase;
            }
        }

        // 6) Último fallback: menu
        if (!$toPath) $toPath = 'dashboard';

        // monta URL final
        $toUrl = rtrim($BASE_URL, '/') . '/' . ltrim($toPath, '/');
        return $toUrl;
    }

    private static function fileExistsInApp(string $appBasePath, string $file): bool
    {
        // Ex.: /Applications/AMPPS/www + /FullConex + /lista_internacao.php
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $path = $doc . $appBasePath . '/' . ltrim($file, '/');
        return is_file($path);
    }
}
