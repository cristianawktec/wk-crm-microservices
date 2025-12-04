const fs = require('fs');
const path = require('path');
(async () => {
  try {
    const { chromium } = require('playwright-core');

    const possible = [
      'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
      'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
      '/usr/bin/google-chrome',
      '/usr/bin/chromium-browser',
      '/snap/bin/chromium'
    ];
    let chromePath = process.env.CHROME_PATH || possible.find(p => fs.existsSync(p));
    if (!chromePath) {
      console.error('No Chrome/Chromium executable found. Set CHROME_PATH env var to its path.');
      process.exit(2);
    }

    console.log('Using browser executable:', chromePath);

    // Build chrome args
    const chromeArgs = ['--no-sandbox', '--disable-dev-shm-usage'];
      const hostHeader = process.env.HOST_HEADER;
      if (hostHeader) {
        try {
          const resolverRule = `MAP ${hostHeader} 127.0.0.1`;
          if (!chromeArgs.some(a => a.startsWith('--host-resolver-rules'))) {
            chromeArgs.push(`--host-resolver-rules=${resolverRule}`);
            console.log('Added chrome arg:', `--host-resolver-rules=${resolverRule}`);
          }
        } catch (e) {
          console.log('Failed to add host-resolver-rules:', e && e.message);
        }
      }

    // NOTE: we avoid using host-resolver rules to prevent ERR_INVALID_ARGUMENT in some Chrome versions.
    // Instead we navigate to an IP (127.0.0.1) and set the Host header via extra HTTP headers below.
    const browser = await chromium.launch({
      executablePath: chromePath,
      headless: true,
      args: chromeArgs
    });

    const context = await browser.newContext();
    const page = await context.newPage();

    page.on('console', msg => {
      try {
        console.log('BROWSER-CONSOLE', msg.type(), msg.text());
      } catch (e) {
        console.log('BROWSER-CONSOLE-ERR', e.message);
      }
    });

    page.on('pageerror', err => {
      console.log('PAGE-ERROR', err && (err.stack || err.message || String(err)));
    });

    page.on('requestfailed', request => {
      const failure = request.failure();
      console.log('REQUEST-FAILED', request.method(), request.url(), failure && failure.errorText);
    });

    // Log requests and responses (including XHRs)
    page.on('request', request => {
      try {
        const r = request;
        console.log('REQUEST', r.method(), r.url());
      } catch (e) {}
    });

    page.on('response', async response => {
      try {
        const url = response.url();
        const status = response.status();
        console.log('RESPONSE', status, url);
        const headers = response.headers();
        if (headers['content-type']) console.log('  content-type:', headers['content-type']);
      } catch (e) {}
    });

    let url = process.argv[2] || 'http://127.0.0.1:4300';
    console.log('Navigating to', url);

    // If HOST_HEADER env var is set, send it as an extra HTTP header (useful for local proxy tests)
    if (hostHeader) {
      console.log('Using Host header:', hostHeader);
        // If the provided URL uses 127.0.0.1, replace it with the hostname so navigation matches Host header.
        try {
          url = url.replace('127.0.0.1', hostHeader);
        } catch (e) {}
      await page.setExtraHTTPHeaders({ Host: hostHeader });
    }

    try {
      const resp = await page.goto(url, { waitUntil: 'load', timeout: 15000 });
      console.log('Navigation status:', resp && resp.status());
    } catch (e) {
      console.log('Goto error:', e.message);
    }

    // wait a bit for scripts to run
    await page.waitForTimeout(3000);

    // capture HTML
    try {
      const html = await page.content();
      console.log('---PAGE HTML START---');
      console.log(html.slice(0, 2000));
      if (html.length > 2000) console.log('---HTML TRUNCATED, length=' + html.length + '---');
      console.log('---PAGE HTML END---');
    } catch (e) {
      console.log('Failed to get page content:', e.message);
    }

    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error('Script error:', err && err.stack ? err.stack : err);
    process.exit(1);
  }
})();
