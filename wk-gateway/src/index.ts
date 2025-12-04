import express from 'express';
import { createProxyMiddleware } from 'http-proxy-middleware';

const app = express();

// Basic health route
app.get('/health', (_req, res) => {
  res.json({ status: 'ok', service: 'wk-gateway' });
});

// Simple root
app.get('/', (_req, res) => {
  res.send('WK Gateway - stub');
});

// Proxy all /api requests to the Laravel container within the compose network.
// The Laravel service is available as `wk-crm-laravel:8000` from other containers.
app.use(
  '/api',
  createProxyMiddleware({
    target: 'http://wk-crm-laravel:8000',
    changeOrigin: true,
    logLevel: 'info',
    pathRewrite: { '^/api': '/api' },
    onProxyReq: (proxyReq, req, res) => {
      // preserve incoming headers if needed
    },
  })
);

const port = process.env.PORT ? parseInt(process.env.PORT, 10) : 3000;
app.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`Gateway service running on port ${port}`);
});
console.log('Gateway service running');
