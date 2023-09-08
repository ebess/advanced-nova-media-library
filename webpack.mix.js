let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss');
let postcssImport = require('postcss-import');

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .postCss('resources/css/field.css', 'dist/css/', [postcssImport(), tailwindcss('tailwind.config.js')])
  .vue({ version: 3 })
  .nova('workup/nova-translatable-field')
