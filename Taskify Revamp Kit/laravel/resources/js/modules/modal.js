/* Modal open/close. Attach to any element with data-toggle="modal" data-target="#id".
   Close via [data-dismiss="modal"] inside, ESC, or backdrop click.
*/
export function initModal() {
  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-toggle="modal"]');
    if (opener) {
      e.preventDefault();
      const target = document.querySelector(opener.dataset.target);
      if (target) open(target);
      return;
    }
    const dismiss = e.target.closest('[data-dismiss="modal"]');
    if (dismiss) { close(dismiss.closest('.modal-host')); return; }

    // Backdrop click
    if (e.target.classList?.contains('overlay-backdrop')) {
      close(e.target.closest('.modal-host'));
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.modal-host[data-open="true"]').forEach(close);
  });

  function open(host) {
    host.dataset.open = 'true';
    host.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    host.dispatchEvent(new CustomEvent('modal:open'));
  }
  function close(host) {
    if (!host) return;
    host.dataset.open = 'false';
    host.setAttribute('hidden', '');
    if (!document.querySelector('.modal-host[data-open="true"], .offcanvas-host[data-open="true"]')) {
      document.body.style.overflow = '';
    }
    host.dispatchEvent(new CustomEvent('modal:close'));
  }
}
