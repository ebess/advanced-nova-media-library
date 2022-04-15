let mix = require('laravel-mix')
let path = require('path')

mix.setPublicPath('dist')
  .vue({version: 3})
  .webpackConfig({
    externals: {
      vue: 'Vue',
    },
    output: {
      uniqueName: 'vendor/package',
    }
  })
  .js('resources/js/field.js', 'js')
  .sass('resources/sass/field.scss', 'css')
  .alias({
    'laravel-nova': path.join(__dirname, '../../vendor/laravel/nova/resources/js/mixins/packages.js'),
  })
