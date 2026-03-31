const mix = require('laravel-mix');

mix.setPublicPath('public');

// --- СБОРКА ДЛЯ САЙТА (Корень /public) ---
mix.js('assets/site/js/app.js', 'js/site.js');
mix.sass('assets/site/scss/app.scss', 'css/site.css');
mix.copyDirectory('assets/site/img', 'public/img');
mix.copyDirectory('assets/site/fonts', 'public/fonts');

// --- СБОРКА ДЛЯ АДМИНКИ (Папка /public/admin) ---
mix.js('assets/admin/js/app.js', 'admin/js/admin.js');
mix.sass('assets/admin/scss/app.scss', 'admin/css/admin.css');
mix.copyDirectory('assets/admin/img', 'public/admin/img');
mix.copyDirectory('assets/admin/fonts', 'public/admin/fonts');

mix.version();
mix.disableNotifications();
