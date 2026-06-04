/* Style guide chrome: copy buttons, active nav, search, theme toggle. */

// ---- theme toggle ----
document.addEventListener('click', (e) => {
  if (e.target.closest('[data-toggle="theme"]')) {
    const cur = document.documentElement.dataset.theme || 'light';
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('ds-theme', next);
  }
});
const savedTheme = localStorage.getItem('ds-theme');
if (savedTheme) document.documentElement.dataset.theme = savedTheme;

// ---- copy buttons on every <pre> ----
document.querySelectorAll('.ds-code').forEach(host => {
  const pre = host.querySelector('pre');
  if (!pre) return;
  const btn = document.createElement('button');
  btn.className = 'copy';
  btn.type = 'button';
  btn.textContent = 'Copy';
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(pre.textContent.trim());
      btn.textContent = 'Copied'; btn.classList.add('copied');
      setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 1400);
    } catch {}
  });
  host.appendChild(btn);
});

// ---- scroll-spy: highlight active nav link ----
const sections = [...document.querySelectorAll('.ds-section[id]')];
const links = new Map();
document.querySelectorAll('.ds-nav-link').forEach(a => {
  const id = (a.getAttribute('href') || '').replace('#', '');
  if (id) links.set(id, a);
});
const obs = new IntersectionObserver((entries) => {
  entries.forEach(en => {
    const link = links.get(en.target.id);
    if (!link) return;
    if (en.isIntersecting) {
      links.forEach(l => l.classList.remove('on'));
      link.classList.add('on');
    }
  });
}, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });
sections.forEach(s => obs.observe(s));

// ---- search: filter nav links ----
const search = document.getElementById('ds-search');
if (search) {
  search.addEventListener('input', () => {
    const q = search.value.trim().toLowerCase();
    document.querySelectorAll('.ds-nav-link').forEach(link => {
      const match = !q || link.textContent.toLowerCase().includes(q);
      link.style.display = match ? '' : 'none';
    });
    document.querySelectorAll('.ds-nav-group').forEach(g => {
      // hide group label if all following links until next group are hidden
      let next = g.nextElementSibling;
      let anyShown = false;
      while (next && !next.classList.contains('ds-nav-group')) {
        if (next.classList.contains('ds-nav-link') && next.style.display !== 'none') { anyShown = true; break; }
        next = next.nextElementSibling;
      }
      g.style.display = anyShown ? '' : 'none';
    });
  });
  // ⌘K / Ctrl+K focuses search
  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault(); search.focus(); search.select();
    }
  });
}

// ---- click an icon card → copy its name ----
document.querySelectorAll('.ds-icon-card').forEach(card => {
  card.addEventListener('click', async () => {
    const name = card.dataset.icon;
    try { await navigator.clipboard.writeText(name); } catch {}
    const lbl = card.querySelector('.ds-icon-name');
    const orig = lbl.textContent;
    lbl.textContent = 'copied!';
    setTimeout(() => lbl.textContent = orig, 1000);
  });
});


/* ---- Chart tooltips (inline copy of /modules/charts.js for the style guide) ---- */
(function() {
  let tt;
  function ensure() {
    if (tt) return tt;
    tt = document.createElement('div');
    tt.className = 'chart-tt'; tt.dataset.visible = 'false';
    document.body.appendChild(tt);
    return tt;
  }
  function esc(s){ return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }
  function show(target, e) {
    const raw = target.getAttribute('data-tt'); if (!raw) return;
    let p; try { p = JSON.parse(raw); } catch { return; }
    const t = ensure();
    const rows = p.rows || [{ label: p.label, value: p.value, color: p.color }];
    let html = '';
    if (p.title) html += '<span class="chart-tt-label">' + esc(p.title) + '</span>';
    else if (p.label && rows.length > 1) html += '<span class="chart-tt-label">' + esc(p.label) + '</span>';
    rows.forEach(r => {
      if (rows.length === 1 && !p.title) {
        html += '<span class="chart-tt-label">' + esc(r.label||'') + '</span>';
        html += '<span class="chart-tt-value">' + esc(String(r.value)) + '</span>';
      } else {
        html += '<div class="chart-tt-row">';
        if (r.color) html += '<span class="chart-tt-swatch" style="background:' + r.color + '"></span>';
        html += '<span style="opacity:0.8">' + esc(r.label||'') + '</span>';
        html += '<span class="chart-tt-value" style="margin-left:auto">' + esc(String(r.value)) + '</span>';
        html += '</div>';
      }
    });
    t.innerHTML = html;
    move(e); t.dataset.visible = 'true';
  }
  function move(e) {
    if (!tt) return;
    tt.style.left = e.clientX + 'px';
    tt.style.top  = e.clientY + 'px';
  }
  function hide() { if (tt) tt.dataset.visible = 'false'; }
  document.addEventListener('mouseover', (e) => {
    const t = e.target.closest('[data-tt]'); if (!t) return;
    t.classList.add('is-hover');
    const parent = t.closest('[data-chart-tooltip="true"]');
    if (parent && t.classList.contains('donut-segment')) {
      parent.querySelectorAll('.donut-segment').forEach(s => { if (s !== t) s.classList.add('is-faded'); });
    }
    show(t, e);
  });
  document.addEventListener('mousemove', (e) => { if (tt?.dataset.visible === 'true') move(e); });
  document.addEventListener('mouseout', (e) => {
    const t = e.target.closest('[data-tt]'); if (!t) return;
    t.classList.remove('is-hover');
    const parent = t.closest('[data-chart-tooltip="true"]');
    if (parent) parent.querySelectorAll('.donut-segment').forEach(s => s.classList.remove('is-faded'));
    hide();
  });
})();
