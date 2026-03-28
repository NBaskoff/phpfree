<?php vh_layout('body'); ?>

	<h1>Добро пожаловать, <?= $userName ?>!</h1>

	<p>Это содержимое главной страницы, которое автоматически обернуто в макет <strong>body.php</strong>.</p>

	<div style="background: #f4f4f4; padding: 10px;">
		<strong>Данные из контроллера:</strong>
		<ul>
			<li>Текущее время: <?= date('H:i:s') ?></li>
			<li>Ваш ID: <?= $userId ?></li>
		</ul>
	</div>

<?php vh_section_start('scripts'); ?>
	<script>
        //console.log('Привет! Этот скрипт был передан из main.php в низ body.php');
        //alert('Шаблонизатор работает!');
	</script>
<?php vh_section_end(); ?>