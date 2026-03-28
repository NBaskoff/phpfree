<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Мой сайт' ?></title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        header { border-bottom: 1px solid #ccc; margin-bottom: 20px; }
        footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; color: #666; }
    </style>
	<link rel="stylesheet" href="<?= vh_mix('/css/app.css') ?>">
</head>
<body>

<header>
    <strong>Название Проекта</strong>
</header>

<main>
    <!-- Сюда вставится код из main.php -->
    <?= $content ?>
</main>

<footer>
    &copy; <?= date('Y') ?> Мой PHP Шаблонизатор
</footer>

<script src="<?= vh_mix('/js/app.js') ?>"></script>

<!-- Сюда попадут скрипты, обернутые в vh_section_start('scripts') -->
<?= vh_section_get('scripts') ?>


</body>
</html>