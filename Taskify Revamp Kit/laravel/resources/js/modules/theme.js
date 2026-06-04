/* Theme toggle. Persists in localStorage. */
export function initTheme() {
  const root = document.documentElement;
  const stored = localStorage.getItem('theme');
  if (stored) root.dataset.theme = stored;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle="theme"]');
    if (!btn) return;
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    localStorage.setItem('theme', next);
  });
}
