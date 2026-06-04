/* ============================================================
   Taskify — JS entry
   All modules are vanilla, attribute-driven, event-delegated.
   Initialize once at boot; new DOM added later is picked up
   automatically via document-level listeners.
   ============================================================ */

import { initTheme } from './modules/theme.js';
import { initAppearance } from './modules/appearance.js';
import { initDropdown } from './modules/dropdown.js';
import { initModal } from './modules/modal.js';
import { initOffcanvas } from './modules/offcanvas.js';
import { initPalette } from './modules/palette.js';
import { initMultiselect } from './modules/multiselect.js';
import { initTable } from './modules/table.js';
import { initCharts } from './modules/charts.js';
import { Toast } from './modules/toast.js';

initAppearance();   // applies persisted theme + hue + active style + density
initTheme();        // header sun/moon button still toggles theme
initDropdown();
initModal();
initOffcanvas();
initPalette();
initMultiselect();
initTable();
initCharts();    // adds hover tooltips to all SVG charts

// Expose toast globally for inline-fired notifications
window.Toast = Toast;
