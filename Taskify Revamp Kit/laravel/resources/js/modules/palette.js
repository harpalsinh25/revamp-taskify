/* Command palette: ⌘K / Ctrl+K to open.
   Markup: <div class="palette-host" id="palette" hidden>…</div>
   Items inside <a class="palette-item" data-href="/path">…</a> are filtered by input.
*/
export function initPalette() {
  const host = document.getElementById('palette');
  if (!host) return;
  const input  = host.querySelector('input');
  const items  = () => [...host.querySelectorAll('.palette-item')];

  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault(); open();
    }
    if (e.key === 'Escape' && host.dataset.open === 'true') close();
  });
  host.addEventListener('click', (e) => {
    if (e.target.classList?.contains('overlay-backdrop')) close();
  });
  input?.addEventListener('input', filter);
  input?.addEventListener('keydown', nav);

  function open() {
    host.dataset.open = 'true'; host.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    input.value = ''; filter();
    setTimeout(() => input.focus(), 10);
  }
  function close() {
    host.dataset.open = 'false'; host.setAttribute('hidden', '');
    document.body.style.overflow = '';
  }
  function filter() {
    const q = (input.value || '').toLowerCase();
    let firstVisible = null;
    items().forEach(it => {
      const match = !q || it.textContent.toLowerCase().includes(q);
      it.hidden = !match;
      it.dataset.active = 'false';
      if (match && !firstVisible) firstVisible = it;
    });
    if (firstVisible) firstVisible.dataset.active = 'true';
  }
  function nav(e) {
    const visible = items().filter(it => !it.hidden);
    const cur = visible.findIndex(it => it.dataset.active === 'true');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      visible.forEach(v => v.dataset.active = 'false');
      visible[Math.min(cur + 1, visible.length - 1)].dataset.active = 'true';
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      visible.forEach(v => v.dataset.active = 'false');
      visible[Math.max(cur - 1, 0)].dataset.active = 'true';
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const active = visible.find(v => v.dataset.active === 'true');
      if (active?.dataset.href) window.location.href = active.dataset.href;
    }
  }
}
