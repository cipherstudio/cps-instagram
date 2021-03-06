const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    resolve: {
        alias: {
            'photo-tags': '../resources/assets/js/jquery-plugins/jquery.photo-tags.js'  // relative to node_modules
        }
    }
});

mix.js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/instagram.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .sass('resources/assets/sass/instagram.scss', 'public/css');
