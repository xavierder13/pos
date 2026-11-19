import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    // antd's shared core is one ~500 kB chunk (≈165 kB gzip); pages load lazily
    chunkSizeWarningLimit: 800,
  },
  server: {
    host: '0.0.0.0',   // ← expose to outside the container
    port: 5173,
    watch: {
      usePolling: true  // ← needed for file watching inside Docker
    }
  }
})
