<?php
/* admin_permissao.php  —  Gestão de permissões por usuário (Criar/Editar/Deletar) */

require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/message.php';
include_once __DIR__ . '/dao/permissionDao.php';
include_once __DIR__ . '/dao/usuarioDao.php';

/* =========================
   SESSÃO E GUARDA DE ACESSO
   ========================= */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/FullConex',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/* DEV OPCIONAL: admin quick */
if (isset($_GET['dev_admin']) && $_GET['dev_admin'] === '1') {
    $_SESSION['id_usuario'] = 1;
    $_SESSION['ativo']      = 's';
    $_SESSION['cargo']      = 'Diretoria';
    $_SESSION['nivel']      = 5;
    header("Location: {$BASE_URL}admin_permissao.php");
    exit;
}

/* DEBUG OPCIONAL */
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Arquivo: " . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
    echo "URL: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n\n";
    $keys = ['id_usuario', 'ativo', 'cargo', 'nivel', 'email_user', 'usuario_user'];
    foreach ($keys as $k) {
        $v = $_SESSION[$k] ?? null;
        echo $k . ' = ' . var_export($v, true) . "\n";
    }
    exit;
}

/* Guard de login */
if (empty($_SESSION['id_usuario'])) {
    $next = urlencode($_SERVER['REQUEST_URI'] ?? '/FullConex/admin_permissao.php');
    header("Location: {$BASE_URL}index.php?next={$next}");
    exit;
}

/* Checagem de Diretoria */
$cargo  = (string)($_SESSION['cargo'] ?? '');
$nivel  = (string)($_SESSION['nivel'] ?? '');
$ativo  = strtolower((string)($_SESSION['ativo'] ?? ''));
$idUser = (int)($_SESSION['id_usuario'] ?? 0);

$norm = function ($txt) {
    $txt = mb_strtolower(trim((string)$txt), 'UTF-8');
    $c   = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
    $txt = $c !== false ? $c : $txt;
    return preg_replace('/[^a-z]/', '', $txt);
};

$isDiretoria = in_array($norm($cargo), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || in_array($norm($nivel), ['diretoria', 'diretor', 'administrador', 'admin', 'board'], true)
    || ((int)$nivel === -1);

if (!$idUser || $ativo !== 's' || !$isDiretoria) {
    http_response_code(403);
    die('Acesso negado. Requer cargo/nível: Diretoria.');
}

/* CSRF e DADOS */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$permDao = new PermissionDAO($conn, $BASE_URL);
$rows    = $permDao->findAllWithUsers();

/* UI */
include_once __DIR__ . '/templates/header.php';
?>
<style>
/* ====== Layout geral da tabela ====== */
.table-perms {
    border-top: 2px solid #5e2363;
    border-collapse: separate;
}

.table-perms th,
.table-perms td {
    vertical-align: middle;
    padding: .85rem .9rem;
}

.table-perms tbody tr:hover td {
    background: #faf7ff;
}

/* ====== Cabeçalho no padrão do sistema ====== */
.table-perms thead tr {
    background: #5e2363 !important;
}

.table-perms thead th {
    color: #fff !important;
    border-color: #5e2363 !important;
    font-weight: 600;
    vertical-align: middle;
}

/* ====== Badge de data ====== */
.badge-updated {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .2px;
    background: #fff;
    border: 1px solid #e5e7eb;
}

/* ====== Largura total ====== */
.container-fluid {
    max-width: 100% !important;
}

/* ====== Checkboxes refinados ====== */
.table-perms td.text-center {
    white-space: nowrap;
}

.perm-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .15rem;
    border-radius: .5rem;
    transition: background-color .15s ease;
}

td.text-center .perm-wrapper:hover {
    background-color: #f5f3ff;
}

/* hover suave */

/* Ícone estilizado (appearance none) */
.perm-checkbox {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
    display: inline-grid;
    place-content: center;
    cursor: pointer;
    vertical-align: middle;
    transition: border-color .15s ease, box-shadow .15s ease, transform .05s ease;
}

/* “tick” (check) */
.perm-checkbox::before {
    content: "";
    width: 10px;
    height: 10px;
    transform: scale(0);
    transition: transform .12s ease-in-out;
    clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 18%, 80% 0, 43% 62%);
    background: currentColor;
    /* herda a cor setada por ação */
}

/* Marcado */
.perm-checkbox:checked {
    border-color: currentColor;
}

.perm-checkbox:checked::before {
    transform: scale(1);
}

/* Hover/focus */
.perm-checkbox:hover {
    box-shadow: 0 0 0 4px rgba(94, 35, 99, .10);
}

.perm-checkbox:active {
    transform: scale(.98);
}

.perm-checkbox:focus-visible {
    outline: 2px solid #5e2363;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Cores por ação (suaves e consistentes) */
.perm-checkbox[data-field="create"] {
    color: #22c55e;
}

/* verde */
.perm-checkbox[data-field="edit"] {
    color: #f59e0b;
}

/* laranja */
.perm-checkbox[data-field="delete"] {
    color: #ef4444;
}

/* vermelho */

/* Linha alterada (feedback sutil) */
tr.table-warning td {
    background: #fff8e7;
}
</style>

<div class="container-fluid mt-3 px-4">
    <h3 class="mb-3">Permissões por Usuário</h3>
    <p class="text-muted mb-3">Habilite as funções <strong>Criar</strong>, <strong>Editar</strong> e
        <strong>Deletar</strong> para cada usuário.
    </p>

    <div class="mb-2 d-flex gap-2 flex-wrap">
        <button id="btnSaveAll" class="btn btn-primary">Salvar alterações</button>
        <button id="btnSelectAllCreate" class="btn btn-outline-secondary btn-sm">Marcar Criar (todos)</button>
        <button id="btnSelectAllEdit" class="btn btn-outline-secondary btn-sm">Marcar Editar (todos)</button>
        <button id="btnSelectAllDelete" class="btn btn-outline-secondary btn-sm">Marcar Deletar (todos)</button>
        <button id="btnClearAll" class="btn btn-outline-danger btn-sm">Limpar todos</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-perms">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th class="text-center">Criar</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Deletar</th>
                    <th>Atualizado em</th>
                </tr>
            </thead>
            <tbody id="tbodyPerms">
                <?php foreach ($rows as $i => $r): ?>
                <tr data-user-id="<?= (int)$r['id_user'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($r['nome'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '') ?></td>

                    <td class="text-center">
                        <label class="perm-wrapper" title="Criar">
                            <input type="checkbox" class="perm-checkbox" data-field="create"
                                <?= ((int)$r['can_create'] === 1 ? 'checked' : '') ?>>
                        </label>
                    </td>

                    <td class="text-center">
                        <label class="perm-wrapper" title="Editar">
                            <input type="checkbox" class="perm-checkbox" data-field="edit"
                                <?= ((int)$r['can_edit'] === 1 ? 'checked' : '') ?>>
                        </label>
                    </td>

                    <td class="text-center">
                        <label class="perm-wrapper" title="Deletar">
                            <input type="checkbox" class="perm-checkbox" data-field="delete"
                                <?= ((int)$r['can_delete'] === 1 ? 'checked' : '') ?>>
                        </label>
                    </td>

                    <td>
                        <?php
                            // dd/mm/aaaa hh:mm
                            $fmtUpdated = '';
                            if (!empty($r['updated_at'])) {
                                $ts = strtotime($r['updated_at']);
                                if ($ts !== false) $fmtUpdated = date('d/m/Y H:i', $ts);
                            }
                            ?>
                        <?php if ($fmtUpdated): ?>
                        <span class="badge bg-light text-dark badge-updated">
                            <?= htmlspecialchars($fmtUpdated) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="7" class="text-muted">Nenhum usuário encontrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    const csrf = "<?= $csrf ?>";
    const tbody = document.getElementById('tbodyPerms');
    const dirty = new Set();

    tbody.addEventListener('change', (e) => {
        const cb = e.target;
        if (!cb.classList.contains('perm-checkbox')) return;
        const tr = cb.closest('tr');
        if (tr) {
            tr.classList.add('table-warning');
            dirty.add(tr.dataset.userId);
        }
    });

    const setAll = (field, value) => {
        document.querySelectorAll(`.perm-checkbox[data-field="${field}"]`).forEach(cb => {
            if (cb.checked !== value) {
                cb.checked = value;
                const tr = cb.closest('tr');
                if (tr) {
                    tr.classList.add('table-warning');
                    dirty.add(tr.dataset.userId);
                }
            }
        });
    };

    document.getElementById('btnSelectAllCreate')?.addEventListener('click', () => setAll('create', true));
    document.getElementById('btnSelectAllEdit')?.addEventListener('click', () => setAll('edit', true));
    document.getElementById('btnSelectAllDelete')?.addEventListener('click', () => setAll('delete', true));
    document.getElementById('btnClearAll')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                const tr = cb.closest('tr');
                if (tr) {
                    tr.classList.add('table-warning');
                    dirty.add(tr.dataset.userId);
                }
            }
        });
    });

    document.getElementById('btnSaveAll')?.addEventListener('click', async () => {
        const payload = {
            csrf,
            perm: {}
        };

        document.querySelectorAll('tr[data-user-id]').forEach(tr => {
            const uid = tr.dataset.userId;
            if (!dirty.has(uid)) return;
            const get = f => tr.querySelector(`.perm-checkbox[data-field="${f}"]`)?.checked ?
                '1' : '0';
            payload.perm[uid] = {
                create: get('create'),
                edit: get('edit'),
                delete: get('delete')
            };
        });

        if (Object.keys(payload.perm).length === 0) {
            alert('Nada para salvar.');
            return;
        }

        try {
            const res = await fetch('process_permissoes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || data.status !== 'ok') throw new Error(data.message || 'Erro ao salvar');

            dirty.clear();
            document.querySelectorAll('tr.table-warning').forEach(tr => tr.classList.remove(
                'table-warning'));
            alert('Permissões atualizadas com sucesso!');
        } catch (err) {
            console.error(err);
            alert('Falha ao salvar: ' + err.message);
        }
    });
})();
</script>

<?php include_once __DIR__ . '/templates/footer.php'; ?>