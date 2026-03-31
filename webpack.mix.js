const mix = require('laravel-mix');

// папка для манифеста и скомпилированных файлов
mix.setPublicPath('public');

// --- сборка для сайта (корень /public) ---
mix.js('assets/js/app.js', 'js/site.js');
mix.sass('assets/scss/app.scss', 'css/site.css');
mix.copyDirectory('assets/img', 'public/img');
mix.copyDirectory('assets/fonts', 'public/fonts');

// --- сборка для админки (папка /public/admin) ---
mix.js('assets/admin/js/app.js', 'admin/js/admin.js');
mix.sass('assets/admin/scss/app.scss', 'admin/css/admin.css');
mix.copyDirectory('assets/admin/img', 'public/admin/img');
mix.copyDirectory('assets/admin/fonts', 'public/admin/fonts');

// включение версионности для сброса кэша
mix.version();

// отключение системных уведомлений
mix.disableNotifications();
