/* Toast notifications. Usage: Toast.show({type:'success', title:'Saved'}); */
let host;
function ensureHost() {
  if (host) return host;
  host = document.createElement('div');
  host.className = 'toast-host';
  document.body.appendChild(host);
  return host;
}
export const Toast = {
  show({ type = 'info', title = '', message = '', duration = 3500 } = {}) {
    const node = document.createElement('div');
    node.className = 'toast toast-' + type;
    node.innerHTML = `
      <span class="toast-icon">●</span>
      <div class="toast-body">
        ${title ? `<div class="toast-title">${title}</div>` : ''}
        ${message ? `<div>${message}</div>` : ''}
      </div>`;
    ensureHost().appendChild(node);
    setTimeout(() => {
      node.classList.add('leaving');
      node.addEventListener('animationend', () => node.remove(), { once: true });
    }, duration);
  },
  success(opts){ this.show({...opts, type:'success'}); },
  error(opts)  { this.show({...opts, type:'error'}); },
  info(opts)   { this.show({...opts, type:'info'}); },
  warn(opts)   { this.show({...opts, type:'warn'}); },
};
