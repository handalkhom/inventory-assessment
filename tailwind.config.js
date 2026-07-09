/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/Filament/**/*.php",
    "./app/Livewire/**/*.php",
    "./vendor/filament/**/*.blade.php",
    "./vendor/livewire/livewire/dist/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}