const mix = require('laravel-mix');
const path = require('path');

mix.setPublicPath('dist')
  .js('resources/js/field.js', 'js').vue({version: 3})
  .webpackConfig({
    externals: {
      vue: 'Vue',
    },
    output: {
      uniqueName: 'vendor/package',
    }
  })
  .alias({
    'laravel-nova': path.join(__dirname, '../../laravel/nova/resources/js/mixins/packages.js'),
    'axios': path.join(__dirname, 'node_modules/axios'),
    'lodash': path.join(__dirname, 'node_modules/lodash'),
    'form-backend-validation': path.join(__dirname, 'node_modules/form-backend-validation'),
    '@babel/plugin-transform-runtime': path.join(__dirname, 'node_modules/@babel/plugin-transform-runtime'),
    '@babel/runtime': path.join(__dirname, 'node_modules/@babel/runtime'),
    'vuex': path.join(__dirname, 'node_modules/vuex'),
    '@inertiajs/inertia': path.join(__dirname, 'node_modules/@inertiajs/inertia'),
  })
  .sass('resources/sass/field.scss', 'css')
