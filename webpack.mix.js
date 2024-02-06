const mix = require('laravel-mix');

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

// mix.sass('resources/sass/app.scss', 'public/css')
//     .js('resources/js/app.js', 'public/js')
//     .vue()
    // .minify('public/css/app.css')
    // .minify('public/css/easy-responsive-tabs.css')
    // .minify('public/css/estilo_general.css')
    // .minify('public/css/jquery.nestable.css')
    // .minify('public/css/style-web.css')
    // .minify('public/js/app.js', 'public/js/app.min.js');

    mix.vue();
