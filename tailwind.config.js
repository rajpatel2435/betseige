/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    './templates/**/*.twig',
    './templates/*.twig',
    './Components/**/*.twig',
    './Components/**/**/*.twig',
    './assets/**/*.js',
     './Components/**/*.php',
    './functions/**/*.php'
  ],
  darkMode: 'selector', // you can set it to 'false' or 'true or 'media' if you prefer
  theme: {

    backgroundColor:{
      'lightGrayNew':'rgba(29 29 30/0.8)'
    },
    screens: {
      'sm': '640px',
      // => @media (min-width: 640px) { ... }

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }

      
    },
    extend: {
      brightness: {
        25: '.25',
        250: '4.5',
      }
    }
  },
  variants: {
    extend: {}
  },
  plugins: []
}
