import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    // Tailwind v4 via Vite plugin (الرسمية)
    tailwindcss(),

    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',

        // Filament theme entry (لازم موجود عشان ->viteTheme يشتغل)
        'resources/css/filament/theme.css',

        'resources/js/pages/home.js',
      ],

      // تقدر تخليها true، أو تحدد المسارات اللي تبيها تتراقب
      refresh: [
        'resources/views/**',
        'app/Livewire/**',
        'routes/**',
      ],
    }),
  ],
});
