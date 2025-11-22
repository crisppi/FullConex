// js/pages/hub_paciente.js
(() => {
  if (window.__hubPacienteInited) return;
  window.__hubPacienteInited = true;

  // ================== CONFIG ==================
  const API_URL_INT = 'ajax/internacoes_paciente.php';
  const API_OVERVIEW = 'ajax/overview_paciente.php';
  const API_CONTAS = 'ajax/contas_paciente.php';

  const LIMIT = 10;
  const CONTAS_LIMIT = 10;

  // ================== STATE ==================
  const state = {
    // Internações
    loadedInternacoes: false,
    page: 1,
    total: 0,
    sort: 'data_intern_int',
    dir: 'DESC',
    q: ''
  };
  const overviewState = { loaded: false };
  const stateContas = { loaded: false, page: 1, total: 0 };

  // ================== UTILS ==================
  const log = (...a) => console.log('[hub_paciente]', ...a);

  const debounce = (fn, wait = 150) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), wait); }; };

  const on = (root, evt, sel, handler) => {
    if (!root) return;
    root.addEventListener(evt, (e) => {
      const t = e.target.closest(sel);
      if (!t || !root.contains(t)) return;
      handler(e, t);
    });
  };

  const getParam = (n) => new URL(window.location.href).searchParams.get(n);

  const getPacienteId = () => {
    const hub = document.getElementById('hubPaciente'); if (hub?.dataset.id) return parseInt(hub.dataset.id, 10);
    const hidden = document.getElementById('id_paciente'); if (hidden?.value) return parseInt(hidden.value, 10);
    const qs = getParam('id_paciente'); if (qs) return parseInt(qs, 10);
    return null;
  };

  const fmtDateBr = (d) => (d && d !== '0000-00-00' ? d : '—');

  const esc = (s) => (s ?? '').toString()
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  // === Localiza a SENHA (prioriza senha_int) ===
  function getSenha(row) {
    if (!row || typeof row !== 'object') return '';
    if (row.senha_int != null && String(row.senha_int).trim()) return String(row.senha_int).trim();
    if (typeof window.HUB_SENHA_FIELD === 'string' && window.HUB_SENHA_FIELD in row) {
      const v = row[window.HUB_SENHA_FIELD]; return (v ?? '').toString().trim();
    }
    const aliases = ['senha', 'senha_internacao', 'senha_atendimento', 'senha_aut', 'senha_guia', 'senha_pedido', 'senha_autorizacao', 'num_senha', 'numero_senha', 'nr_senha', 'n_senha', 'senha_atd', 'senha_atend', 'Senha'];
    for (const k of aliases) { if (k in row) { const v = row[k]; if (v != null && String(v).trim()) return String(v).trim(); } }
    const blobs = ['obs', 'observacao', 'observacoes', 'detalhes', 'resumo'];
    for (const k of blobs) {
      if (typeof row[k] === 'string') {
        const m = row[k].match(/senha\s*[:\-]?\s*([A-Za-z0-9\-_.\/]+)/i);
        if (m?.[1]) return m[1].trim();
      }
    }
    return '';
  }

  // ================== INTERNACOES: RENDER ==================
  const renderRows = (rows = []) => {
    const table = document.getElementById('tblInternacoes');
    const tbody = table?.querySelector('tbody');
    if (!tbody) return;

    // garante coluna "Senha" no thead (segunda coluna)
    const thead = table.querySelector('thead');
    if (thead) {
      const trHead = thead.querySelector('tr') || thead.appendChild(document.createElement('tr'));
      const ths = [...trHead.querySelectorAll('th')];
      const hasSenhaTh = ths.some(th => th.textContent.trim().toLowerCase().includes('senha'));
      if (!hasSenhaTh) {
        const senhaTh = document.createElement('th');
        senhaTh.textContent = 'Senha';
        if (ths.length) ths[0].insertAdjacentElement('afterend', senhaTh);
        else {
          trHead.innerHTML = `
            <th>ID-INT</th>
            <th>Senha</th>
            <th>Admissão</th>
            <th>Alta</th>
            <th>Unidade / Leito</th>
            <th>Médico</th>
            <th>Status</th>
            <th>Prorrog.</th>
            <th>Ações</th>`;
        }
      }
    }

    const COLS = (() => {
      const ths = table.querySelectorAll('thead tr:first-child th');
      return ths.length || 9;
    })();

    tbody.innerHTML = '';

    if (!rows.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="${COLS}" class="text-center text-muted py-3">Nenhuma internação encontrada.</td>`;
      tbody.appendChild(tr);
      return;
    }

    rows.forEach((row) => {
      const iid = row.id_internacao ?? row.id ?? '';
      const senha = (row.senha_int ?? row.senha ?? getSenha(row) ?? '').toString().trim();

      const adm = row.admissao ?? row.data_intern_int ?? row.data_admissao ?? row.data ?? '';
      const alta = row.alta ?? row.data_alta_alt ?? row.data_alta ?? '';
      const unidade = row.unidade ?? row.nome_hosp ?? row.hospital ?? row.estabelecimento ?? '—';
      const leito = row.leito ?? row.acomodacao_int ?? row.acomodacao ?? '';
      const medico = row.medico ?? row.medico_responsavel ?? row.crm_int ?? row.crm ?? '—';
      const status =
        row.status ??
        (String(row.internado_int).toLowerCase() === 'n'
          ? 'Alta'
          : (String(row.internado_int).toLowerCase() === 's' ? 'Internado' : '—'));
      const pror = Number(row.prorrogacoes ?? row.qtd_prorrog ?? 0) || 0;
      const isAlta = String(status).toLowerCase() === 'alta' ||
        String(row.internado_int).toLowerCase() === 'n';

      const tr = document.createElement('tr');
      tr.classList.add('row-int');
      tr.dataset.idInt = iid;
      tr.tabIndex = 0;

      tr.innerHTML = `
        <td>${esc(iid)}</td>
        <td>${esc(senha || '—')}</td>
        <td>${fmtDateBr(adm)}</td>
        <td>${fmtDateBr(alta)}</td>
        <td>${esc(unidade)}${leito ? ' / ' + esc(leito) : ''}</td>
        <td>${esc(medico)}</td>
        <td>${esc(status)}</td>
        <td>${pror}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-primary" data-action="ver-int" data-id-int="${esc(iid)}">Ver</button>
          ${isAlta ? '' : `
            <button class="btn btn-sm btn-outline-secondary" data-action="editar-int" data-id-int="${esc(iid)}">Editar</button>
            <button class="btn btn-sm btn-outline-info" data-action="alta-int" data-id-int="${esc(iid)}">Alta</button>
          `}
        </td>
      `;

      tbody.appendChild(tr);
    });
  };

  const renderTotal = (total) => { const el = document.getElementById('int-total'); if (el) el.textContent = `Total: ${total}`; };

  const renderPager = (total, page, limit) => {
    const pager = document.getElementById('int-pager'); if (!pager) return;
    pager.innerHTML = '';
    const pages = Math.max(1, Math.ceil((total || 0) / limit));
    const mk = (label, p, dis = false, act = false) => {
      const li = document.createElement('li'); li.className = `page-item${dis ? ' disabled' : ''}${act ? ' active' : ''}`;
      const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.textContent = label; if (!dis) a.dataset.page = p; li.appendChild(a); return li;
    };
    pager.appendChild(mk('‹', page - 1, page <= 1));
    const win = 5; let s = Math.max(1, page - Math.floor(win / 2)); let e = Math.min(pages, s + win - 1); if (e - s + 1 < win) s = Math.max(1, e - win + 1);
    for (let i = s; i <= e; i++) pager.appendChild(mk(String(i), i, false, i === page));
    pager.appendChild(mk('›', page + 1, page >= pages));
  };

  // ================== INTERNACOES: ACTIONS ==================
  // ================== INTERNACOES: ACTIONS ==================
  const initInternacoesActions = () => {
    const table = document.getElementById('tblInternacoes');
    if (!table) return;

    // Botão: Ver
    on(table, 'click', '[data-action="ver-int"]', (_e, btn) => {
      const id = btn.getAttribute('data-id-int');
      if (!id) return;
      window.location.href = `show_internacao.php?id_internacao=${id}`;
    });

    // Botão: Editar
    on(table, 'click', '[data-action="editar-int"]', (_e, btn) => {
      const id = btn.getAttribute('data-id-int');
      if (!id) return;
      window.location.href = `edit_internacao.php?id_internacao=${id}`;
    });

    // Botão: Alta
    on(table, 'click', '[data-action="alta-int"]', (_e, btn) => {
      const id = btn.getAttribute('data-id-int');
      if (!id) return;
      window.location.href = `edit_alta.php?id_internacao=${id}`;
    });

    // Clique na linha → agir como "Ver"
    on(table, 'click', 'tbody tr', (e, tr) => {
      // Se clicou em controles/botões, não intercepta
      if (e.target.closest('a,button,[data-action],input,select,textarea,label,i')) return;
      const verBtn = tr.querySelector('[data-action="ver-int"]');
      if (verBtn) verBtn.click();
    });

    // Enter na linha → agir como "Ver"
    table.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      const tr = e.target.closest('tbody tr.row-int');
      if (!tr) return;
      const verBtn = tr.querySelector('[data-action="ver-int"]');
      if (verBtn) verBtn.click();
    });
  };


  // ================== INTERNACOES: FILTER / LOAD ==================
  const initInternacoesFilter = () => {
    const input = document.getElementById('buscaInternacoes');
    const table = document.getElementById('tblInternacoes');
    if (!input || !table) return;
    const tbody = table.querySelector('tbody'); if (!tbody) return;

    const filter = debounce((term) => {
      const t = term.trim().toLowerCase();
      const rows = tbody.querySelectorAll('tr'); let vis = 0;
      rows.forEach((tr) => {
        if (tr.dataset.empty === '1') return;
        const text = tr.textContent.toLowerCase();
        const show = !t || text.indexOf(t) !== -1;
        tr.style.display = show ? '' : 'none';
        if (show) vis++;
      });
      let emptyRow = tbody.querySelector('tr[data-empty="1"]');
      if (!emptyRow) {
        emptyRow = document.createElement('tr'); emptyRow.dataset.empty = '1';
        emptyRow.innerHTML = `<td colspan="9" class="text-center text-muted py-3">Nenhuma internação encontrada.</td>`;
        tbody.appendChild(emptyRow);
      }
      emptyRow.style.display = vis === 0 ? '' : 'none';
    }, 150);

    input.addEventListener('input', (e) => filter(e.target.value));
  };

  const initPagerClicks = () => {
    const pager = document.getElementById('int-pager'); if (!pager) return;
    on(pager, 'click', 'a.page-link', (e, a) => {
      e.preventDefault(); const p = parseInt(a.dataset.page, 10);
      if (Number.isFinite(p) && p !== state.page) { state.page = p; loadInternacoes(); }
    });
  };

  function hasAjax(path) { return !!path; }

  const loadInternacoes = async () => {
    const pacId = getPacienteId();
    if (!pacId) { log('id_paciente não encontrado.'); return; }

    // Fallback: PRECARREGADO pelo PHP
    if (Array.isArray(window.PRELOADED_INT)) {
      const rows = window.PRELOADED_INT;
      state.total = rows.length;
      renderRows(rows);
      renderTotal(state.total);
      renderPager(state.total, 1, Math.max(LIMIT, state.total));
      initInternacoesActions();
      state.loadedInternacoes = true;
      return;
    }

    if (!hasAjax(API_URL_INT)) return;

    const params = new URLSearchParams({ id_paciente: pacId, page: state.page, limit: LIMIT, sort: state.sort, dir: state.dir, q: state.q });
    try {
      const res = await fetch(`${API_URL_INT}?${params}`, { cache: 'no-store' });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Falha ao buscar internações');

      state.total = data.total || 0;
      renderRows(data.rows || []);
      renderTotal(state.total);
      renderPager(state.total, state.page, LIMIT);
      initInternacoesActions();
      state.loadedInternacoes = true;
    } catch (err) {
      console.error('[hub] erro ao carregar internações (AJAX).', err);
      renderRows([]); renderTotal(0); renderPager(0, 1, LIMIT);
    }
  };

  // ================== OVERVIEW (apenas Internação atual) ==================
  async function loadOverview() {
    const pacId = getPacienteId(); if (!pacId) return;

    if (window.PRELOADED_OVERVIEW && typeof window.PRELOADED_OVERVIEW === 'object') {
      renderOverview(window.PRELOADED_OVERVIEW); overviewState.loaded = true; return;
    }
    if (!hasAjax(API_OVERVIEW)) return;

    try {
      const params = new URLSearchParams({ id_paciente: pacId });
      const res = await fetch(`${API_OVERVIEW}?${params}`, { cache: 'no-store' });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Falha no overview');
      renderOverview(data); overviewState.loaded = true;
    } catch (err) {
      console.error('[overview] req error', err); renderOverview(null);
    }
  }

  function renderOverview(data) {
    const pane = document.getElementById('tab-overview'); if (!pane) return;
    let box = pane.querySelector('#overviewBox'); if (!box) { box = document.createElement('div'); box.id = 'overviewBox'; pane.innerHTML = ''; pane.appendChild(box); }

    if (!data) { box.innerHTML = `<div class="alert alert-light border text-secondary">Não foi possível carregar a visão geral agora.</div>`; return; }

    const int = data.internacao_atual;
    const isAlta = String(int?.status || '').toLowerCase() === 'alta';

    const intBody = int ? `
      <div class="small text-secondary mb-1">ID: ${esc(int.id_internacao)}</div>
      <div class="mb-1"><span class="badge badge-soft ${isAlta ? 'text-success' : 'text-warning'}">${esc(int.status || '—')}</span></div>
      <div>Admissão: ${fmtDateBr(int.data || int.data_admissao || int.data_intern_int)} ${int.hora ? ('às ' + esc(int.hora)) : ''}</div>
      ${int.alta ? `<div>Alta: ${fmtDateBr(int.alta)} ${int.hora_alta ? ('às ' + esc(int.hora_alta)) : ''}</div>` : ''}
      <div>Acomodação: ${esc(int.acomodacao || int.acomodacao_int || '—')}</div>
      <div>Especialidade: ${esc(int.especialidade || int.especialidade_int || '—')}</div>
    ` : `
      <div class="text-muted">Paciente sem internação ativa.</div>
      <div class="mt-2"><a class="btn btn-primary btn-sm" href="cad_internacao.php?id_paciente=${encodeURIComponent(getPacienteId() || '')}">Lançar Internação</a></div>
    `;

    box.innerHTML = `
      <div class="row g-3 align-items-stretch">
        <div class="col-12 d-flex">
          <div class="card ov-card ov-int flex-fill">
            <div class="card-body d-flex flex-column">
              <div class="ov-head"><span class="ov-icon"><i class="fa-solid fa-bed-pulse"></i></span><h6 class="ov-title">Internação atual</h6></div>
              ${intBody}
            </div>
          </div>
        </div>
      </div>`;
  }

  // ================== CONTAS ==================
  function renderContasResumo(summary) {
    const resumo = document.querySelector('#tab-contas .card:nth-of-type(1) .card-body');
    if (resumo) {
      resumo.innerHTML = `<h6 class="mb-2">Resumo</h6>
      <div class="row g-2 small">
        <div class="col-12"><strong>Valor apresentado:</strong> R$ ${Number(summary?.soma_apresentado || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
        <div class="col-12"><strong>Glosa total:</strong> R$ ${Number(summary?.soma_glosa_total || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
        <div class="col-12"><strong>Valor final:</strong> R$ ${Number(summary?.soma_final || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
      </div>`;
    }
    const alertas = document.querySelector('#tab-contas .card:nth-of-type(2) .card-body');
    if (alertas) {
      alertas.innerHTML = `<h6 class="mb-2">Status</h6>
      <div class="small">Abertos: <strong>${summary?.abertos ?? 0}</strong></div>
      <div class="small">Em auditoria: <strong>${summary?.em_auditoria ?? 0}</strong></div>
      <div class="small">Encerrados: <strong>${summary?.encerrados ?? 0}</strong></div>`;
    }
  }

  function ensureContasTable() {
    const pane = document.getElementById('tab-contas'); if (!pane) return null;
    let wrap = pane.querySelector('#contasWrap'); if (!wrap) {
      wrap = document.createElement('div'); wrap.id = 'contasWrap'; wrap.className = 'mt-3';
      wrap.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Contas do paciente</h6></div>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle" id="tblContas">
            <thead>
              <tr>
                <th>Internação</th><th>Conta</th><th>Hospital</th><th>Período</th>
                <th class="text-end">Apresentado</th><th class="text-end">Glosa</th><th class="text-end">Final</th>
                <th>Status</th><th>Parcial</th><th>Ações</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <small id="cont-total"></small>
          <nav><ul class="pagination pagination-sm mb-0" id="cont-pager"></ul></nav>
        </div>`;
      pane.appendChild(wrap);
    } return wrap;
  }

  function renderContasRows(rows) {
    ensureContasTable();
    const tbody = document.querySelector('#tblContas tbody'); if (!tbody) return;
    tbody.innerHTML = '';
    if (!rows || !rows.length) { const tr = document.createElement('tr'); tr.innerHTML = `<td colspan="10" class="text-center text-muted py-3">Nenhuma conta encontrada.</td>`; tbody.appendChild(tr); return; }
    rows.forEach(r => {
      const tr = document.createElement('tr');
      const valorId = r.id_valor ? String(r.id_valor) : '';
      const capeanteId = r.id_capeante ? String(r.id_capeante) : '';
      const intId = r.id_internacao ? String(r.id_internacao) : '';

      const baseRahUrl = `cad_capeante_rah.php?id_capeante=${encodeURIComponent(capeanteId)}${intId ? `&id_internacao=${encodeURIComponent(intId)}` : ''}`;
      const rahViewUrl = baseRahUrl;
      const rahEditUrl = valorId
        ? `edit_capeante_rah.php?id_valor=${encodeURIComponent(valorId)}`
        : `edit_capeante_rah.php?id_capeante=${encodeURIComponent(capeanteId)}${intId ? `&id_internacao=${encodeURIComponent(intId)}` : ''}`;
      const rahPreviewUrl = `export_capeante_rah_pdf.php?id_capeante=${encodeURIComponent(capeanteId)}&download=0`;

      tr.innerHTML = `
        <td>${esc(r.id_internacao)}</td>
        <td>#${esc(r.id_capeante)}</td>
        <td>${esc(r.hospital || '—')}</td>
        <td>${esc(r.periodo || '—')}</td>
        <td class="text-end">R$ ${Number(r.valor_apresentado || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
        <td class="text-end">R$ ${Number(r.glosa_total || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
        <td class="text-end">R$ ${Number(r.valor_final || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
        <td>${esc(r.status || '—')}</td>
        <td>${esc(r.parcial || '—')}</td>
        <td class="d-flex gap-1 flex-wrap">
          <a class="btn btn-sm btn-outline-primary" href="${rahEditUrl}" title="Editar RAH da conta">Editar RAH</a>
          <a class="btn btn-sm btn-outline-success" href="${rahViewUrl}" title="Criar/visualizar RAH">RAH</a>
          <button class="btn btn-sm btn-outline-secondary" type="button" data-action="preview-rah" data-url="${rahPreviewUrl}" title="Visualizar RAH em tela">Ver RAH</button>
        </td>`;
      tbody.appendChild(tr);
    });
  }
  function renderContasTotal(total) { const el = document.getElementById('cont-total'); if (el) el.textContent = `Total: ${total}`; }
  function renderContasPager(total, page, limit) {
    const pager = document.getElementById('cont-pager'); if (!pager) return;
    pager.innerHTML = ''; const pages = Math.max(1, Math.ceil((total || 0) / limit));
    const mk = (label, p, dis = false, act = false) => {
      const li = document.createElement('li'); li.className = `page-item${dis ? ' disabled' : ''}${act ? ' active' : ''}`;
      const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; if (!dis) a.dataset.page = p; a.textContent = label; li.appendChild(a); return li;
    };
    pager.appendChild(mk('‹', page - 1, page <= 1)); const win = 5; let s = Math.max(1, page - Math.floor(win / 2)); let e = Math.min(pages, s + win - 1); if (e - s + 1 < win) s = Math.max(1, e - win + 1);
    for (let i = s; i <= e; i++) pager.appendChild(mk(String(i), i, false, i === page)); pager.appendChild(mk('›', page + 1, page >= pages));
  }
  function initContasPagerClicks() {
    const pager = document.getElementById('cont-pager'); if (!pager) return;
    pager.addEventListener('click', (e) => { const a = e.target.closest('a.page-link'); if (!a) return; e.preventDefault(); const p = parseInt(a.dataset.page, 10); if (Number.isFinite(p) && p !== stateContas.page) { stateContas.page = p; loadContas(); } });
  }
  const rahModalEl = document.getElementById('rahPreviewModal');
  const rahFrameEl = document.getElementById('rahPreviewFrame');
  let rahModalInstance = null;
  function ensureRahModal() {
    if (!rahModalEl || !window.bootstrap || !bootstrap.Modal) return null;
    if (!rahModalInstance) {
      rahModalInstance = new bootstrap.Modal(rahModalEl, {});
      rahModalEl.addEventListener('hidden.bs.modal', () => {
        if (rahFrameEl) rahFrameEl.src = '';
      });
    }
    return rahModalInstance;
  }
  function openRahPreview(url) {
    if (!url) return;
    const modal = ensureRahModal();
    if (!modal || !rahFrameEl) {
      window.open(url, '_blank');
      return;
    }
    rahFrameEl.src = url + (url.includes('?') ? '&' : '?') + 'inline=1#toolbar=0';
    modal.show();
  }
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="preview-rah"]');
    if (!btn) return;
    e.preventDefault();
    const url = btn.getAttribute('data-url');
    openRahPreview(url);
  });
  async function loadContas() {
    const pacId = getPacienteId(); if (!pacId) return; ensureContasTable();

    if (window.PRELOADED_CONTAS && typeof window.PRELOADED_CONTAS === 'object') {
      const { rows = [], total = rows.length, summary = {} } = window.PRELOADED_CONTAS;
      stateContas.total = total; renderContasResumo(summary); renderContasRows(rows); renderContasTotal(stateContas.total); renderContasPager(stateContas.total, stateContas.page, CONTAS_LIMIT); initContasPagerClicks(); stateContas.loaded = true; return;
    }

    if (!hasAjax(API_CONTAS)) return;

    const params = new URLSearchParams({ id_paciente: pacId, page: stateContas.page, limit: CONTAS_LIMIT });
    try {
      const res = await fetch(`${API_CONTAS}?${params}`, { cache: 'no-store' }); const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Falha em contas');
      stateContas.total = data.total || 0; renderContasResumo(data.summary || {}); renderContasRows(data.rows || []); renderContasTotal(stateContas.total); renderContasPager(stateContas.total, stateContas.page, CONTAS_LIMIT); initContasPagerClicks(); stateContas.loaded = true;
    } catch (err) {
      console.error('[contas] req error', err); renderContasResumo(null); renderContasRows([]); renderContasTotal(0); renderContasPager(0, 1, CONTAS_LIMIT);
    }
  }

  // ================== BOOT ==================
  const boot = () => {
    if (typeof window.HUB_SENHA_FIELD === 'undefined') window.HUB_SENHA_FIELD = 'senha_int';

    // Internações
    initInternacoesFilter(); initPagerClicks();

    // Tabs (apenas as 3)
    document.querySelectorAll('[data-bs-toggle="pill"], [data-bs-toggle="tab"]').forEach((tab) => {
      tab.addEventListener('shown.bs.tab', (e) => {
        const target = e.target.getAttribute('data-bs-target');
        if (target === '#tab-internacoes') {
          if (!state.loadedInternacoes) loadInternacoes();
          initInternacoesFilter(); initInternacoesActions(); initPagerClicks();
        }
        if (target === '#tab-overview' && !overviewState.loaded) loadOverview();
        if (target === '#tab-contas' && !stateContas.loaded) loadContas();
      });
    });

    // Se alguma aba já vier ativa
    const activePane = document.querySelector('.tab-pane.show.active');
    if (activePane) {
      if (activePane.id === 'tab-internacoes') loadInternacoes();
      if (activePane.id === 'tab-overview' && !overviewState.loaded) loadOverview();
      if (activePane.id === 'tab-contas' && !stateContas.loaded) loadContas();
    }
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
