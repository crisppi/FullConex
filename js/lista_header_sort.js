(() => {
  const NO_SORT_LABELS = ['acao', 'acoes'];

  function normalizeText(value) {
    return (value || '')
      .toString()
      .trim()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function isDateBr(value) {
    return /^\d{2}\/\d{2}\/\d{4}(?:\s+\d{2}:\d{2})?$/.test(value);
  }

  function parseDateBr(value) {
    const [datePart, timePart] = value.split(/\s+/);
    const [dd, mm, yyyy] = datePart.split('/').map(Number);
    if (!dd || !mm || !yyyy) return Number.NaN;
    const [hh, min] = (timePart || '00:00').split(':').map(Number);
    return new Date(yyyy, mm - 1, dd, hh || 0, min || 0, 0, 0).getTime();
  }

  function parseNumberLike(value) {
    const cleaned = value
      .replace(/\s+/g, '')
      .replace(/\.(?=\d{3}(?:\D|$))/g, '')
      .replace(',', '.')
      .replace(/[^\d.-]/g, '');
    if (!cleaned || cleaned === '-' || cleaned === '.') return Number.NaN;
    return Number(cleaned);
  }

  function getComparable(rawValue) {
    const value = (rawValue || '').toString().trim();
    if (!value) return { type: 'text', value: '' };

    if (isDateBr(value)) {
      const ts = parseDateBr(value);
      if (!Number.isNaN(ts)) return { type: 'number', value: ts };
    }

    const num = parseNumberLike(value);
    if (!Number.isNaN(num)) return { type: 'number', value: num };

    return { type: 'text', value: value.toLowerCase() };
  }

  function getCellText(row, columnIndex) {
    const cell = row.cells[columnIndex];
    return cell ? (cell.textContent || '').trim() : '';
  }

  function shouldSkipHeaderCell(th) {
    if (!th) return true;
    if (th.dataset.sort === 'false' || th.classList.contains('no-sort')) return true;
    if (th.colSpan && th.colSpan > 1) return true;
    if (th.querySelector('.th-sortable, .sort-icons')) return true;
    if (th.querySelector('a[href*="sort_"], a[href*="sort-field"], a[href*="sort_field"]')) return true;
    if (th.querySelector('button, input, select, textarea')) return true;

    const label = normalizeText(th.textContent);
    if (!label) return true;
    if (NO_SORT_LABELS.includes(label)) return true;
    return false;
  }

  function clearIndicators(headerCells) {
    headerCells.forEach((th) => {
      th.classList.remove('js-sort-active-asc', 'js-sort-active-desc');
      const icon = th.querySelector('.js-sort-indicator');
      if (icon) icon.textContent = '⇅';
    });
  }

  function sortTableByColumn(table, columnIndex, direction, headerCells) {
    const tbody = table.tBodies && table.tBodies[0];
    if (!tbody) return;
    const rows = Array.from(tbody.rows);
    if (!rows.length) return;

    const decorated = rows.map((row, idx) => ({
      row,
      idx,
      key: getComparable(getCellText(row, columnIndex)),
    }));

    decorated.sort((a, b) => {
      if (a.key.type === 'number' && b.key.type === 'number') {
        if (a.key.value === b.key.value) return a.idx - b.idx;
        return direction === 'asc' ? a.key.value - b.key.value : b.key.value - a.key.value;
      }
      const cmp = a.key.value.localeCompare(b.key.value, 'pt-BR', { numeric: true, sensitivity: 'base' });
      if (cmp === 0) return a.idx - b.idx;
      return direction === 'asc' ? cmp : -cmp;
    });

    const frag = document.createDocumentFragment();
    decorated.forEach((item) => frag.appendChild(item.row));
    tbody.appendChild(frag);

    clearIndicators(headerCells);
    const active = headerCells[columnIndex];
    if (!active) return;
    active.classList.add(direction === 'asc' ? 'js-sort-active-asc' : 'js-sort-active-desc');
    const icon = active.querySelector('.js-sort-indicator');
    if (icon) icon.textContent = direction === 'asc' ? '▲' : '▼';
  }

  function prepareTable(table) {
    const thead = table.tHead;
    const tbody = table.tBodies && table.tBodies[0];
    if (!thead || !tbody) return;
    if (!tbody.rows || tbody.rows.length <= 1) return;

    const headRow = thead.rows[thead.rows.length - 1];
    if (!headRow) return;
    const headerCells = Array.from(headRow.cells);
    if (!headerCells.length) return;

    let hasSortable = false;
    headerCells.forEach((th, idx) => {
      if (shouldSkipHeaderCell(th)) return;
      hasSortable = true;
      th.classList.add('js-sortable-header');
      th.setAttribute('role', 'button');
      th.setAttribute('tabindex', '0');
      th.setAttribute('aria-label', `Ordenar coluna ${idx + 1}`);
      if (!th.querySelector('.js-sort-indicator')) {
        const marker = document.createElement('span');
        marker.className = 'js-sort-indicator';
        marker.textContent = '⇅';
        th.appendChild(marker);
      }

      const triggerSort = () => {
        const currentDir = th.dataset.sortDir === 'asc' ? 'asc' : 'desc';
        const nextDir = currentDir === 'asc' ? 'desc' : 'asc';
        headerCells.forEach((cell) => {
          delete cell.dataset.sortDir;
        });
        th.dataset.sortDir = nextDir;
        sortTableByColumn(table, idx, nextDir, headerCells);
      };

      th.addEventListener('click', triggerSort);
      th.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          triggerSort();
        }
      });
    });

    if (!hasSortable) return;
  }

  function applyHeaderSortOnListPages() {
    const pathname = (window.location.pathname || '').toLowerCase();
    const page = (pathname.split('/').pop() || '').toLowerCase();

    const isLegacyListRoute = page.startsWith('list_');
    const isFriendlyListRoute = pathname.includes('/listas/') || pathname.endsWith('/censo/lista');
    const isPacientesRoute = pathname.endsWith('/pacientes') || page === 'pacientes';

    if (!isLegacyListRoute && !isFriendlyListRoute && !isPacientesRoute) return;
    if (page === 'list_internacao.php' || page === 'list_internacao_sem_senha.php') return;

    const tables = document.querySelectorAll('.table-responsive table.table, table.table');
    tables.forEach((table) => prepareTable(table));
  }

  window.applyHeaderSortOnListPages = applyHeaderSortOnListPages;
  document.addEventListener('DOMContentLoaded', applyHeaderSortOnListPages);
})();
