let mix = require('laravel-mix')

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .sass('resources/sass/field.scss', 'css')
  .vue({ version: 3 })
  .nova('workup/nova-translatable-field')
