import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'path'

export default defineConfig({
  plugins: [tailwindcss(), vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'assets'),
    },
  },
  build: {
    outDir: 'public/build',
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'assets/app.ts'),
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://app:80',
        changeOrigin: true,
      },
    },
  },
})
