const mix = require('laravel-mix');

// Папка, где будет лежать mix-manifest.json и скомпилированные файлы
mix.setPublicPath('public');

mix.js('assets/js/app.js', 'js/app.js').version();
mix.sass('assets/scss/app.scss', 'css/app.css').version();
//mix.copyDirectory('assets/img', 'public/images');