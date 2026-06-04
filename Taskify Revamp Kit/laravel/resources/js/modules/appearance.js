/* Appearance preferences.
   Persists: theme, signal hue, active-menu style, density.
   Applies on boot so the choice survives reloads.

   Storage shape: localStorage['taskify.appearance'] = JSON.stringify({
     theme: 'light' | 'dark' | 'system',
     signalHue: 0..360,
     activeStyle: 'notch' | … (19 variants),
     density: 'compact' | 'cozy' | 'comfortable',
   })
*/

const KEY = 'taskify.appearance';

const DEFAULTS = {
  theme: 'system',
  signalHue: 130,
  activeStyle: 'notch',
  density: 'cozy',
};

export function getPrefs() {
  try {
    return { ...DEFAULTS, ...JSON.parse(localStorage.getItem(KEY) || '{}') };
  } catch {
    return { ...DEFAULTS };
  }
}

export function setPrefs(patch) {
  const next = { ...getPrefs(), ...patch };
  localStorage.setItem(KEY, JSON.stringify(next));
  applyAppearance(next);
  // broadcast so other tabs / open panels sync
  window.dispatchEvent(new CustomEvent('appearance:change', { detail: next }));
  return next;
}

export function applyAppearance(prefs = getPrefs()) {
  const root = document.documentElement;

  // theme: light | dark | system
  if (prefs.theme === 'system') {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    root.dataset.theme = mq.matches ? 'dark' : 'light';
    // re-apply when system changes
    if (!applyAppearance._mqBound) {
      mq.addEventListener('change', () => applyAppearance(getPrefs()));
      applyAppearance._mqBound = true;
    }
  } else {
    root.dataset.theme = prefs.theme;
  }

  // signal hue: override --signal* tokens with a fresh hue
  const h = prefs.signalHue ?? 130;
  root.style.setProperty('--signal',      `oklch(0.88 0.20 ${h})`);
  root.style.setProperty('--signal-fg',   `oklch(0.18 0.02 ${h})`);
  root.style.setProperty('--signal-soft', `oklch(0.95 0.10 ${h})`);
  root.style.setProperty('--signal-glow', `oklch(0.88 0.20 ${h} / 0.35)`);

  // active menu style: drive [data-active-style] on .app for the sidebar
  root.dataset.activeStyle = prefs.activeStyle;
  document.querySelectorAll('.app').forEach(el => el.dataset.activeStyle = prefs.activeStyle);

  // density: tighten or loosen padding/row-height
  root.dataset.density = prefs.density;
}

export function initAppearance() {
  // Apply on boot
  applyAppearance();

  // Wire form controls (any [data-appearance="<key>"] input/select/button)
  document.addEventListener('input', (e) => {
    const el = e.target.closest('[data-appearance]');
    if (!el) return;
    const key = el.dataset.appearance;
    let value = el.value;
    if (el.type === 'checkbox') value = el.checked;
    if (el.type === 'range' || el.type === 'number') value = Number(value);
    setPrefs({ [key]: value });
  });
  document.addEventListener('click', (e) => {
    const swatch = e.target.closest('[data-hue]');
    if (swatch) setPrefs({ signalHue: Number(swatch.dataset.hue) });

    const card = e.target.closest('[data-active-style-card]');
    if (card) setPrefs({ activeStyle: card.dataset.activeStyleCard });

    const themeBtn = e.target.closest('[data-set-theme]');
    if (themeBtn) setPrefs({ theme: themeBtn.dataset.setTheme });
  });

  // Reset to defaults
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-appearance-reset]')) {
      localStorage.removeItem(KEY);
      applyAppearance();
      window.dispatchEvent(new CustomEvent('appearance:reset'));
    }
  });
}
