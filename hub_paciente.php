<?php
include_once("check_logado.php");
include_once("templates/header.php");
include_once("models/message.php");

include_once("models/seguradora.php");
include_once("dao/seguradoraDao.php");

include_once("models/estipulante.php");
include_once("dao/estipulanteDao.php");

include_once("models/paciente.php");
include_once("dao/pacienteDao.php");


include_once("models/internacao.php");      // se existir
include_once("dao/internacaoDao.php");      // o seu DAO

$internacaoDao = new internacaoDAO($conn, $BASE_URL);  // ajuste o nome da classe se diferente
// DAOs
$pacienteDao = new pacienteDAO($conn, $BASE_URL);
$seguradoraDao = new seguradoraDAO($conn, $BASE_URL);
$estipulanteDao = new estipulanteDAO($conn, $BASE_URL);

// GET
$id_paciente = filter_input(INPUT_GET, "id_paciente");
if (!$id_paciente) {
  echo "<div class='container mt-4'><div class='alert alert-danger'>Paciente não informado.</div></div>";
  include_once("templates/footer.php");
  exit;
}
$internacoes = $internacaoDao->listByPaciente((int)$id_paciente); // já vem com senha_int

// Dados do paciente
$paciente = $pacienteDao->findById($id_paciente); // seu findById retorna array-like com $paciente['0'][campo]
if (!$paciente || !isset($paciente['0'])) {
  echo "<div class='container mt-4'><div class='alert alert-warning'>Paciente não encontrado.</div></div>";
  include_once("templates/footer.php");
  exit;
}
$p = $paciente['0'];

// Helpers de formatação (iguais aos seus)
function formatCpf($cpf)
{
  if (!empty($cpf)) {
    $cpf = preg_replace("/\D/", '', $cpf);
    if (strlen($cpf) == 11) {
      return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
  }
  return $cpf;
}
function formatCep($cep)
{
  if (!empty($cep)) {
    $cep = preg_replace("/\D/", '', $cep);
    if (strlen($cep) == 8) {
      return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
  }
  return $cep;
}
function formatPhone($phone)
{
  if (!empty($phone)) {
    $phone = preg_replace("/\D/", '', $phone);
    if (strlen($phone) == 11) {
      return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
    } elseif (strlen($phone) == 10) {
      return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6, 4);
    }
  }
  return $phone;
}
function formatDateBr($dateYmd)
{
  if (!$dateYmd || $dateYmd == '0000-00-00')
    return '';
  $dt = DateTime::createFromFormat('Y-m-d', $dateYmd);
  return $dt ? $dt->format('d/m/Y') : $dateYmd;
}

// Campos formatados
$cpf_fmt = formatCpf($p['cpf_pac'] ?? '');
$tel1_fmt = formatPhone($p['telefone01_pac'] ?? '');
$tel2_fmt = formatPhone($p['telefone02_pac'] ?? '');
$cep_fmt = formatCep($p['cep_pac'] ?? '');
$nasc_fmt = formatDateBr($p['data_nasc_pac'] ?? '');

// Matrícula formatada considerando RN: MATRICULA + "RN"(se s) + numero_rn_pac
$mat_base = trim((string) ($p['matricula_pac'] ?? ''));
$mat_rn_flag = ($p['recem_nascido_pac'] ?? '') === 's';
$mat_rn_num = isset($p['numero_rn_pac']) && $p['numero_rn_pac'] !== '' ? (string) $p['numero_rn_pac'] : '';
$mat_full = $mat_base . ($mat_rn_flag ? ('RN' . $mat_rn_num) : '');

// Seguradora/Estipulante (se seu findById já deu join, ótimo — senão traga os nomes por ID aqui)
$seguradora_nome = $p['seguradora_seg'] ?? '';
$estipulante_nome = $p['nome_est'] ?? '';

// Endereço compacto
$endereco_fmt = trim(($p['endereco_pac'] ?? '') . ', ' . ($p['numero_pac'] ?? '')) .
  ' - ' . trim(($p['bairro_pac'] ?? '')) .
  ' - ' . trim(($p['cidade_pac'] ?? '') . '/' . ($p['estado_pac'] ?? '')) .
  ' - CEP ' . $cep_fmt;

// Recen nascido
$recem_nascido_pac = $p['recem_nascido_pac'];
$numero_recem_nascido_pac = $p['numero_rn_pac'];

// Iniciais
$nome_str = trim((string) ($p['nome_pac'] ?? ''));
$ini = '';
if ($nome_str) {
  $parts = preg_split('/\s+/', $nome_str);
  $ini = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
}
?>
<!-- Você já tem Bootstrap do header.php. Aqui só estrutura da página -->

<div class="container-fluid py-3">
  <div id="hubPaciente" data-id="<?= $p['id_paciente'] ?>"></div>

  <!-- Cabeçalho do paciente -->
  <div class="card shadow-sm mb-3" style="border-radius:14px;">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
      <div class="d-flex gap-3 align-items-center">
        <div
          style="width:64px;height:64px;border-radius:50%;background:#ecd5f9;display:flex;align-items:center;justify-content:center;font-weight:700;color:#5e2363">
          <?= $ini ?: 'PA' ?>
        </div>
        <div>
          <h4 class="mb-1"><?= htmlspecialchars($nome_str ?: '—') ?></h4>
          <div class="d-flex flex-wrap gap-2 text-secondary small">
            <span><i class="fa-regular fa-id-card me-1"></i>Matrícula:
              <?= htmlspecialchars($mat_full ?: '—') ?></span>
            <span>•</span>
            <span><i class="fa-regular fa-calendar me-1"></i>Nasc.:
              <?= htmlspecialchars($nasc_fmt ?: '—') ?></span>
            <span>•</span>
            <span><i
                class="fa-solid fa-venus-mars me-1"></i><?= htmlspecialchars(strtoupper($p['sexo_pac'] ?? '')) ?></span>
            <span>•</span>
            <span><i class="fa-solid fa-shield-heart me-1"></i>Seg.:
              <?= htmlspecialchars($seguradora_nome ?: '—') ?></span>
            <?php if ($estipulante_nome): ?>
              <span>•</span>
              <span><i class="fa-solid fa-briefcase-medical me-1"></i>Estip.:
                <?= htmlspecialchars($estipulante_nome) ?></span>
            <?php endif; ?>
            <?php if ($recem_nascido_pac !== null): ?>
              <span>•</span>
              <span>
                <i class="fa-solid fa-baby me-1"></i>
                RN: <?= $recem_nascido_pac == 's' ? 'Sim' : 'Não' ?>
                <?php if (!empty($numero_recem_nascido_pac)): ?>
                  - Nº <?= htmlspecialchars($numero_recem_nascido_pac) ?>
                <?php endif; ?>
              </span>
            <?php endif; ?>

          </div>
        </div>
      </div>
      <div class="d-flex flex-column text-end">
        <div class="small text-secondary">CPF</div>
        <div class="fw-semibold"><?= htmlspecialchars($cpf_fmt ?: '—') ?></div>
        <div class="small text-secondary mt-2">Contato</div>
        <div><?= htmlspecialchars($tel2_fmt ?: $tel1_fmt ?: '—') ?></div>
        <div class="small text-secondary mt-2">Endereço</div>
        <div class="text-truncate" style="max-width:420px;" title="<?= htmlspecialchars($endereco_fmt) ?>">
          <?= htmlspecialchars($endereco_fmt) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Abas -->
  <div class="card shadow-sm" style="border-radius:14px;">
    <div class="card-body">
      <ul class="nav nav-pills mb-3" role="tablist">
       
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-internacoes" type="button"
            role="tab">
            <i class="fa-solid fa-bed-pulse me-2"></i>Internações
          </button>
        </li>


        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-contas" type="button"
            role="tab">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Contas
          </button>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade" id="tab-overview" role="tabpanel">
          <div class="alert alert-light border text-secondary">
            Carregando...
          </div>
        </div>

        <!-- Internações -->
        <div class="tab-pane fade show active" id="tab-internacoes" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Histórico de internações</h6>
            <div class="input-group input-group-sm" style="max-width:300px">
              <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
              <input id="buscaInternacoes" type="text" class="form-control" placeholder="Filtrar...">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle" id="tblInternacoes">
              <thead>
                <tr>
                  <th>ID-INT</th>
                  <th>Senha</th> <!-- NOVA COLUNA -->
                  <th>Admissão</th>
                  <th>Alta</th>
                  <th>Unidade / Leito</th>
                  <th>Médico</th>
                  <th>Status</th>
                  <th>Prorrog.</th>
                  <th>Ações</th>
                </tr>
              </thead>

              <tbody>
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <small id="int-total"></small>
            <nav>
              <ul class="pagination pagination-sm mb-0" id="int-pager"></ul>
            </nav>
          </div>
        </div>




        <!-- Antecedentes -->
        <div class="tab-pane fade" id="tab-antecedentes" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Antecedentes e condições</h6>
            <button class="btn btn-outline-secondary btn-sm"><i
                class="fa-solid fa-plus me-2"></i>Novo</button>
          </div>
          <div id="chipsAntecedentes" class="d-flex flex-wrap gap-2">
            <!-- chips com antecedentes (ex.: HAS, DM2, etc.) -->
          </div>
        </div>


        <!-- Autorizações -->
        <div class="tab-pane fade" id="tab-autorizacoes" role="tabpanel">
          <div class="alert alert-light border text-secondary">Sem registros</div>
        </div>

        <!-- Contas -->
        <div class="tab-pane fade" id="tab-contas" role="tabpanel">
          <div class="row g-3">
            <div class="col-12 col-lg-6">
              <div class="card border-0">
                <div class="card-body">
                  <h6 class="mb-2">Resumo</h6>
                  <div class="alert alert-light border text-secondary">Preencher com totalização de
                    contas quando
                    disponível.</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card border-0">
                <div class="card-body">
                  <h6 class="mb-2">Alertas</h6>
                  <div class="alert alert-light border text-secondary">Sem divergências no momento
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /tab-content -->
    </div>
  </div>
</div>

<!-- Modal de visualização do RAH -->
<div class="modal fade" id="rahPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Visualizar RAH</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" style="min-height:70vh;">
        <div class="ratio ratio-16x9 border rounded bg-light">
          <iframe id="rahPreviewFrame" title="Pré-visualização do RAH" style="border:0;" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('rahPreviewModal')?.addEventListener('hidden.bs.modal', function() {
    const frame = document.getElementById('rahPreviewFrame');
    if (frame) frame.src = '';
  });
</script>

</div>

<style>
  :root {
    --brand: #5e2363;
    --brand-700: #4b1c50;
    --brand-800: #431945;
    --brand-100: #f2e8f7;
    --brand-050: #f9f3fc;
  }

  /* Botões principais */
  .btn-primary {
    background-color: var(--brand) !important;
    border-color: var(--brand) !important;
  }

  .btn-primary:hover {
    background-color: var(--brand-700) !important;
    border-color: var(--brand-700) !important;
  }

  .btn-primary:focus,
  .btn-primary:active {
    background-color: var(--brand-800) !important;
    border-color: var(--brand-800) !important;
    box-shadow: 0 0 0 .2rem rgba(94, 35, 99, .25) !important;
  }

  /* “Outline” no tom da marca (vale para os que você já usa: info/secondary) */
  .btn-outline-primary,
  .btn-outline-info,
  .btn-outline-secondary {
    color: var(--brand) !important;
    border-color: var(--brand) !important;
  }

  .btn-outline-primary:hover,
  .btn-outline-info:hover,
  .btn-outline-secondary:hover {
    color: #fff !important;
    background-color: var(--brand) !important;
    border-color: var(--brand) !important;
  }

  .btn-outline-primary:focus,
  .btn-outline-info:focus,
  .btn-outline-secondary:focus {
    box-shadow: 0 0 0 .2rem rgba(94, 35, 99, .25) !important;
  }

  /* Abas (nav-pills) */
  .nav-pills .nav-link {
    color: var(--brand);
  }

  .nav-pills .nav-link:hover {
    background: var(--brand-050);
  }

  .nav-pills .nav-link.active {
    background-color: var(--brand) !important;
  }

  /* Cabeçalhos de tabela suaves no tema */
  .table thead {
    background: var(--brand-100);
  }

  .table thead th {
    color: var(--brand);
    border-color: #eadcf3 !important;
    font-size: 14px;
  }

  .table td {
    font-size: 13px;
  }

  /* Paginação */
  .pagination .page-link {
    color: var(--brand);
    border-color: #e7ddef;
  }

  .pagination .page-item.active .page-link {
    color: #fff;
    background-color: var(--brand);
    border-color: var(--brand);
  }

  .pagination .page-link:hover {
    background: var(--brand-050);
    border-color: var(--brand);
  }

  /* Inputs foco */
  .form-control:focus {
    border-color: var(--brand) !important;
    box-shadow: 0 0 0 .2rem rgba(94, 35, 99, .15) !important;
  }

  .input-group-text {
    background: var(--brand-100);
    color: var(--brand);
    border-color: #eadcf3 !important;
  }

  /* Modal header no tom do sistema */
  #globalModal .modal-header {
    background: var(--brand);
    color: #fff;
  }

  /* Cards “limpinhos” */
  .card {
    border-radius: 14px;
  }

  .card.shadow-sm {
    box-shadow: 0 8px 24px rgba(0, 0, 0, .06) !important;
  }

  /* Badges da marca (se quiser usar) */
  .badge-brand {
    background: var(--brand);
    color: #fff;
  }

  /* ===== Overview cards com destaque visual ===== */
  .ov-card {
    position: relative;
    border: 0 !important;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .06) !important;
    background: #fff;
  }

  /* Faixa lateral de cor */
  .ov-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    border-top-left-radius: 14px;
    border-bottom-left-radius: 14px;
    background: var(--ov-accent, var(--brand));
    opacity: .9;
  }

  /* Cabeçalho do card com ícone */
  .ov-head {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .5rem;
  }

  .ov-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ov-accent-100, var(--brand-100));
    color: var(--ov-accent, var(--brand));
    flex: 0 0 36px;
    font-size: 16px;
  }

  /* Título compacto e forte */
  .ov-title {
    margin: 0;
    font-weight: 600;
    color: var(--ov-accent, var(--brand));
  }

  /* Paletas suaves para cada card */
  :root {
    --teal: #0f766e;
    --teal-100: #d1fae5;
    --amber: #b45309;
    --amber-100: #fef3c7;
  }

  /* Variantes */
  .ov-int {
    --ov-accent: var(--brand);
    --ov-accent-100: var(--brand-100);
  }

  .ov-vis {
    --ov-accent: var(--teal);
    --ov-accent-100: var(--teal-100);
  }

  .ov-recent {
    --ov-accent: var(--amber);
    --ov-accent-100: var(--amber-100);
  }

  /* Badge de status com borda suave (fallback para quem não usa bg-subtle do BS 5.3) */
  .badge-soft {
    border: 1px solid currentColor;
    background: rgba(0, 0, 0, .03);
    font-weight: 600;
  }

  /* Apenas no card do paciente */
  .card-body .small {
    font-size: 0.95rem !important;
    color: #444 !important;
    font-weight: 400;
  }
</style>

<script>
  window.BASE_URL = '<?= rtrim($BASE_URL, '/') . '/' ?>'; // ex: http://localhost/full17.2/
  window.PACIENTE_ID = <?= (int) $id_paciente ?>;
</script>
<script src="<?= $BASE_URL ?>js/hub_paciente.js?v=<?= filemtime('js/hub_paciente.js') ?>"></script>


<?php
// Carrega as internações (precisa vir com 'senha_int' do DAO)
$preloadedInt = $internacaoDao->listByPaciente((int)$id_paciente);
?>
<script>
  // Diz qual campo é a senha e injeta os dados pro JS
  window.HUB_SENHA_FIELD = 'senha_int';
  window.PRELOADED_INT = <?= json_encode($preloadedInt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  // (mantém o que você já tinha)
  window.BASE_URL = '<?= rtrim($BASE_URL, '/') . '/' ?>';
  window.PACIENTE_ID = <?= (int) $id_paciente ?>;
</script>
<script src="<?= rtrim($BASE_URL, '/') ?>/js/pages/hub_paciente.js?v=<?= time() ?>"></script>

<?php include_once("templates/footer.php"); ?>
