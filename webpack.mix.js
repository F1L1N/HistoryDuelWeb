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

mix.scripts([
        'resources/assets/js/jquery-1.11.1.min.js',
        'resources/assets/js/bootstrap.min.js',
        'resources/assets/js/slick.min.js',
        'resources/assets/js/placeholdem.min.js',
        'resources/assets/js/rs-plugin/js/jquery.themepunch.plugins.min.js',
        'resources/assets/js/rs-plugin/js/jquery.themepunch.revolution.min.js',
        //'resources/assets/js/waypoints.min.js',
        //'resources/assets/js/jquery.waypoints.js',
        'resources/assets/js/scripts.js',
        'resources/assets/js/modernizr.custom.32033.js',
    ], 'public/js/app.js')
    .styles([
        'resources/assets/css/vendor/bootstrap.min.css',
        'resources/assets/css/animate.css',
        'resources/assets/css/font-awesome.min.css',
        'resources/assets/css/slick.css',
        'resources/assets/js/rs-plugin/css/settings.css',
        'resources/assets/css/styles.css',
    ], 'public/css/app.css');