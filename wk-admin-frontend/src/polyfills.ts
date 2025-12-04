/**
 * Polyfills for the Angular application.
 *
 * This file ensures Zone.js is loaded before Angular bootstraps.
 */

// Zone JS is required by default for Angular unless you opt-out of zone.js
import 'zone.js';

// Optional: enable task-tracking plugin so testability/task-tracking features work
// (useful for devtools and some Angular tooling). Keep commented if not needed.
// Try to load the task-tracking plugin if available. This is optional and
// only improves developer tooling (testability/task-tracking). We use a
// dynamic import so TypeScript won't complain about `require` in the browser
// environment.
// @ts-ignore: optional dev-only plugin may not exist in all setups
import('zone.js/plugins/task-tracking').catch(() => {
  // ignore if plugin not available
});
