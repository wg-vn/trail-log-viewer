import { createHash } from 'node:crypto';
import { cpSync, readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

// The PHP side reads public/mix-manifest.json (Laravel's mix() helper and
// LogViewer::assetsAreCurrent()), so keep emitting it in Laravel Mix's format.
function mixManifest() {
  return {
    name: 'mix-manifest',
    apply: 'build',
    closeBundle() {
      cpSync('resources/img', 'public/img', { recursive: true });

      const files = ['app.js', 'app.css', ...readdirSync('public/img').map((file) => `img/${file}`)];
      const manifest = Object.fromEntries(files.map((file) => {
        const hash = createHash('md5').update(readFileSync(`public/${file}`)).digest('hex');

        return [`/${file}`, `/${file}?id=${hash}`];
      }));

      writeFileSync('public/mix-manifest.json', JSON.stringify(manifest, null, 4) + '\n');
    },
  };
}

export default defineConfig({
  plugins: [vue(), tailwindcss(), mixManifest()],
  publicDir: false,
  build: {
    outDir: 'public',
    emptyOutDir: true,
    cssCodeSplit: false,
    rolldownOptions: {
      input: 'resources/js/app.js',
      output: {
        // Loaded with a plain <script> tag (or inlined), not as an ES module.
        format: 'iife',
        entryFileNames: 'app.js',
        assetFileNames: ({ names }) => (names.some((name) => name.endsWith('.css')) ? 'app.css' : '[name][extname]'),
        minify: {
          compress: {
            dropConsole: true,
          },
        },
      },
    },
  },
});
