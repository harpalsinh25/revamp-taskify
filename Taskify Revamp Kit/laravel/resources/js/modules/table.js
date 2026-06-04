/* Lightweight table behavior: sort headers + select-all checkbox.
   For larger needs, swap with a server-driven DataTables / Livewire setup.
*/
export function initTable() {
  document.querySelectorAll('table.table').forEach(table => {
    // select-all checkbox
    const all = table.querySelector('thead .check[data-select-all]');
    if (all) {
      all.addEventListener('change', () => {
        table.querySelectorAll('tbody .check[data-select-row]').forEach(c => {
          c.checked = all.checked;
          c.closest('tr').dataset.selected = all.checked ? 'true' : 'false';
        });
      });
    }
    table.querySelectorAll('tbody .check[data-select-row]').forEach(c => {
      c.addEventListener('change', () => {
        c.closest('tr').dataset.selected = c.checked ? 'true' : 'false';
      });
    });

    // sortable columns (client-side, presentation only)
    table.querySelectorAll('th[data-sort]').forEach(th => {
      th.addEventListener('click', () => {
        const cur = th.dataset.sort;
        table.querySelectorAll('th[data-sort]').forEach(t => t.dataset.sort = '');
        th.dataset.sort = cur === 'asc' ? 'desc' : 'asc';
        table.dispatchEvent(new CustomEvent('table:sort', { detail: { col: th.dataset.col, dir: th.dataset.sort } }));
      });
    });
  });
}
