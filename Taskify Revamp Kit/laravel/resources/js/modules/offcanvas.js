/* Offcanvas / drawer. Same protocol as modal:
   data-toggle="offcanvas" data-target="#id"
   data-dismiss="offcanvas"
*/
export function initOffcanvas() {
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-toggle="offcanvas"]');
    if (opener) {
      e.preventDefault();
      const target = document.querySelector(opener.dataset.target);
      if (target) open(target);
      return;
    }
    const dismiss = e.target.closest('[data-dismiss="offcanvas"]');
    if (dismiss) { close(dismiss.closest('.offcanvas-host')); return; }

    if (e.target.classList?.contains('overlay-backdrop')) {
      close(e.target.closest('.offcanvas-host'));
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.offcanvas-host[data-open="true"]').forEach(close);
  });

  function open(host)  { host.dataset.open = 'true';  host.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; }
  function close(host) {
    if (!host) return;
    host.dataset.open = 'false'; host.setAttribute('hidden', '');
    if (!document.querySelector('.modal-host[data-open="true"], .offcanvas-host[data-open="true"]')) {
      document.body.style.overflow = '';
    }
  }
}
