/* Ajax-capable multi-select with tag tokens.
   Markup:
     <div class="multiselect" data-multiselect data-endpoint="/api/users/search" data-name="user_ids[]">
       <div class="tag-strip">
         <input type="text" placeholder="Search…"/>
       </div>
       <div class="dropdown-menu"></div>
     </div>
   Tokens, hidden inputs, debounced fetching, keyboard nav.
*/
export function initMultiselect() {
  document.querySelectorAll('[data-multiselect]').forEach(setup);
}

function setup(root) {
  const strip   = root.querySelector('.tag-strip');
  const input   = root.querySelector('input');
  const menu    = root.querySelector('.dropdown-menu');
  const name    = root.dataset.name;
  const endpoint = root.dataset.endpoint;
  const local   = root.dataset.local ? JSON.parse(root.dataset.local) : null;
  const selected = new Map();
  let debounce;

  input.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => fetchOptions(input.value), 200);
  });
  input.addEventListener('focus', () => { menu.style.display = 'block'; fetchOptions(input.value); });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !input.value && selected.size) {
      const last = [...selected.keys()].pop();
      removeToken(last);
    }
  });
  document.addEventListener('click', (e) => {
    if (!root.contains(e.target)) menu.style.display = 'none';
  });

  async function fetchOptions(q) {
    if (local) return render(local.filter(o => !q || o.label.toLowerCase().includes(q.toLowerCase())));
    if (!endpoint) return;
    menu.innerHTML = '<div class="dropdown-empty">Loading…</div>';
    try {
      const res = await fetch(endpoint + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      render(data.results || data);
    } catch {
      menu.innerHTML = '<div class="dropdown-empty">Failed to load</div>';
    }
  }

  function render(items) {
    if (!items.length) { menu.innerHTML = '<div class="dropdown-empty">No matches</div>'; return; }
    menu.innerHTML = items.map(o => `
      <button type="button" class="dropdown-item" data-value="${o.value}">
        <span>${o.label}</span>
      </button>`).join('');
    menu.querySelectorAll('.dropdown-item').forEach(btn => {
      btn.addEventListener('click', () => {
        addToken(btn.dataset.value, btn.textContent.trim());
        input.value = ''; input.focus();
      });
    });
  }

  function addToken(value, label) {
    if (selected.has(value)) return;
    selected.set(value, label);
    const token = document.createElement('span');
    token.className = 'tag-token';
    token.dataset.value = value;
    token.innerHTML = `<span>${label}</span><button type="button" aria-label="Remove">×</button><input type="hidden" name="${name}" value="${value}"/>`;
    token.querySelector('button').addEventListener('click', () => removeToken(value));
    strip.insertBefore(token, input);
  }
  function removeToken(value) {
    selected.delete(value);
    strip.querySelector(`[data-value="${value}"]`)?.remove();
  }
}
