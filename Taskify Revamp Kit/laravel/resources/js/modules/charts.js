/* Chart tooltips. Attach to any SVG with [data-chart-tooltip="true"].
   Hit targets carry data-tt='{"label":"W3","rows":[{"label":"Tasks","value":48,"color":"var(--signal)"}]}'.
   For convenience, a single { label, value, color } row can be flat:
     data-tt='{"label":"W3","value":48,"color":"var(--signal)"}'
*/

let tooltip;

function ensureTooltip() {
  if (tooltip) return tooltip;
  tooltip = document.createElement('div');
  tooltip.className = 'chart-tt';
  tooltip.dataset.visible = 'false';
  document.body.appendChild(tooltip);
  return tooltip;
}

function show(target, e) {
  const raw = target.getAttribute('data-tt');
  if (!raw) return;
  let payload;
  try { payload = JSON.parse(raw); } catch { return; }

  const tt = ensureTooltip();
  const rows = payload.rows || [{ label: payload.label, value: payload.value, color: payload.color }];

  let html = '';
  if (payload.title) {
    html += '<span class="chart-tt-label">' + escape(payload.title) + '</span>';
  } else if (payload.label && rows.length > 1) {
    html += '<span class="chart-tt-label">' + escape(payload.label) + '</span>';
  }
  rows.forEach(r => {
    if (rows.length === 1 && !payload.title) {
      // Single-row form: label sits on its own line, value beneath
      html += '<span class="chart-tt-label">' + escape(r.label || '') + '</span>';
      html += '<span class="chart-tt-value">' + escape(String(r.value)) + '</span>';
    } else {
      html += '<div class="chart-tt-row">';
      if (r.color) html += '<span class="chart-tt-swatch" style="background:' + r.color + '"></span>';
      html += '<span style="opacity:0.8">' + escape(r.label || '') + '</span>';
      html += '<span class="chart-tt-value" style="margin-left:auto">' + escape(String(r.value)) + '</span>';
      html += '</div>';
    }
  });
  tt.innerHTML = html;
  move(e);
  tt.dataset.visible = 'true';
}

function move(e) {
  if (!tooltip) return;
  tooltip.style.left = e.clientX + 'px';
  tooltip.style.top  = e.clientY + 'px';
}

function hide() {
  if (tooltip) tooltip.dataset.visible = 'false';
}

function escape(s) {
  return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
}

export function initCharts() {
  document.addEventListener('mouseover', (e) => {
    const target = e.target.closest('[data-tt]');
    if (!target) return;
    target.classList.add('is-hover');
    // For donut, fade the other segments
    const parent = target.closest('[data-chart-tooltip="true"]');
    if (parent && target.classList.contains('donut-segment')) {
      parent.querySelectorAll('.donut-segment').forEach(s => {
        if (s !== target) s.classList.add('is-faded');
      });
    }
    show(target, e);
  });
  document.addEventListener('mousemove', (e) => {
    if (tooltip?.dataset.visible === 'true') move(e);
  });
  document.addEventListener('mouseout', (e) => {
    const target = e.target.closest('[data-tt]');
    if (!target) return;
    target.classList.remove('is-hover');
    const parent = target.closest('[data-chart-tooltip="true"]');
    if (parent) {
      parent.querySelectorAll('.donut-segment').forEach(s => s.classList.remove('is-faded'));
    }
    hide();
  });
}
