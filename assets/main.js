import 'vite/modulepreload-polyfill'
import FlyntComponent from './scripts/FlyntComponent'
import Splide from '@splidejs/splide';
import 'lazysizes'

if (import.meta.env.DEV) {
  import('@vite/client')
}

console.log("hihihihih")


function domReady(fn) {
  // If we're early to the party
  document.addEventListener("DOMContentLoaded", fn);
  // If late; I mean on time.
  if (
    document.readyState === "interactive" ||
    document.readyState === "complete"
  ) {
    fn();
  }
}
import.meta.glob([
  '../Components/**',
  '../assets/**',
  '!**/*.js',
  '!**/*.scss',
  '!**/*.php',
  '!**/*.twig',
  '!**/screenshot.png',
  '!**/*.md'
])

window.customElements.define(
  'flynt-component',
  FlyntComponent
)


