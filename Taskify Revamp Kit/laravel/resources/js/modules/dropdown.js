/* Dropdown menus + searchable selects.
   Markup: <div class="dropdown" data-dropdown>
             <button data-dropdown-trigger>…</button>
             <div class="dropdown-menu">…</div>
           </div>
*/
export function initDropdown() {
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-dropdown-trigger]');
    const dd = trigger?.closest('[data-dropdown]');
    if (dd) {
      e.preventDefault();
      const open = dd.dataset.open === 'true';
      closeAll();
      if (!open) dd.dataset.open = 'true';
      return;
    }
    if (!e.target.closest('[data-dropdown]')) closeAll();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  });

  function closeAll() {
    document.querySelectorAll('[data-dropdown][data-open="true"]').forEach(el => el.dataset.open = 'false');
  }
}
