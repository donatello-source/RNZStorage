import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    watch: {
      usePolling: true,
    },
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://symfony_nginx',
        changeOrigin: true,
        secure: false,
      },
    },
  },
});
