import { defineConfig } from 'astro/config';

export default defineConfig({
  srcDir: 'src',
  publicDir: 'public',
  outDir: 'dist',
  server: {
    host: true,
  },
  vite: {
    server: {
      // allow Cloudflare Tunnel preview hosts (any subdomain)
      allowedHosts: ['.trycloudflare.com'],
    },
  },
});
