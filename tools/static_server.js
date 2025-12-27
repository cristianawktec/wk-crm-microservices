const http = require('http');
const fs = require('fs');
const path = require('path');
const root = process.env.ROOT
  ? path.resolve(process.env.ROOT)
  : path.resolve(__dirname, '..', 'wk-admin-frontend', 'dist', 'admin-frontend');
const defaultFile = process.env.DEFAULT_FILE || 'index.html';
const port = process.argv[2] || process.env.PORT || 4500;

const mime = {
  '.html': 'text/html',
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon'
};

const server = http.createServer((req, res) => {
  let filePath = path.join(root, req.url.split('?')[0]);
  if (req.url === '/' || req.url === '') filePath = path.join(root, defaultFile);
  fs.stat(filePath, (err, stats) => {
    if (err || !stats.isFile()) {
      // fallback to index.html for SPA routes
      fs.readFile(path.join(root, defaultFile), (e, data) => {
        if (e) {
          res.writeHead(404);
          res.end('Not found');
          return;
        }
        res.writeHead(200, { 'Content-Type': 'text/html' });
        res.end(data);
      });
      return;
    }
    const ext = path.extname(filePath).toLowerCase();
    const contentType = mime[ext] || 'application/octet-stream';
    fs.readFile(filePath, (e, data) => {
      if (e) {
        res.writeHead(500);
        res.end('Server error');
        return;
      }
      res.writeHead(200, { 'Content-Type': contentType });
      res.end(data);
    });
  });
});

server.listen(port, () => console.log(`Static server listening on http://127.0.0.1:${port}/ (serving ${root})`));
