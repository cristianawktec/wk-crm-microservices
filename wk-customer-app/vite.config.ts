import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  base: '/customer-app/',
  plugins: [vue()],
  build: {
    outDir: 'dist',
  },
});
