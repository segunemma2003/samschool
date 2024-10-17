/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset'
module.exports = {
    presets: [preset],
    content: [
    './app/Filament/**/*.php',
      './resources/**/*.blade.php',
      './resources/views/filament/**/*.blade.php',
      './vendor/filament/**/*.blade.php',
      './resources/**/*.js',
      './resources/**/*.vue',
      './app/Http/Livewire/**/*.php',
      './vendor/filament/**/*.blade.php',

      // Include any other relevant paths
    ],
    theme: {
      extend: {},
    },
    plugins: [],
  };
