const express = require('express');
const app = express();

app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'wk-products-api' });
});

app.get('/', (req, res) => {
  res.send('WK Products API - stub');
});

const port = process.env.PORT || 3001;
app.listen(port, () => {
  // eslint-disable-next-line no-console
  console.log(`wk-products-api listening on port ${port}`);
});
