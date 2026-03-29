<?php
/**
 * Шаблон списка пользователей
 * @var array $users Список объектов UserModel
 * @var string $title Заголовок страницы
 */
?>

<div class="container">
	<h1><?= $title ?></h1>

	<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
		<thead>
		<tr style="background-color: #f4f4f4; text-align: left;">
			<th>ID</th>
			<th>Имя пользователя</th>
			<th>Email</th>
			<th>Роли</th>
			<th>Дата регистрации</th>
			<th>Действия</th>
		</tr>
		</thead>
		<tbody>
		<?php if (empty($users)): ?>
			<tr>
				<td colspan="6" style="text-align: center;">Пользователи не найдены</td>
			</tr>
		<?php else: ?>
			<?php foreach ($users as $user): ?>
				<tr>
					<!-- ID уже типа int благодаря маппингу в BaseModel -->
					<td><?= $user->id ?></td>

					<td>
						<?= $user->name ?>
						<?php if (vh_has_role($user, 'admin')): ?>
							<span style="color: #d9534f; font-weight: bold; font-size: 0.8em;">[ADM]</span>
						<?php endif; ?>
					</td>

					<td><?= $user->email ?></td>

					<td>
						<?php if (!empty($user->roles)): ?>
							<?php
							// Выводим названия всех ролей пользователя через запятую
							echo implode(', ', array_column($user->roles, 'name'));
							?>
						<?php else: ?>
							<span style="color: #ccc; font-style: italic;">нет ролей</span>
						<?php endif; ?>
					</td>

					<!-- Используем наш новый хелпер для форматирования дат -->
					<td><?= vh_date($user->created_at) ?></td>

					<td>
						<?php if (vh_has_role('admin')): ?>
							<!-- Эта кнопка видна только если ТЕКУЩИЙ пользователь — админ -->
							<button style="color: red;">Удалить</button>
						<?php endif; ?>

						<a href="/user/<?= $user->id ?>">Профиль</a>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<p style="margin-top: 15px; font-size: 0.9em; color: #666;">
		Всего записей: <?= count($users) ?>
	</p>
</div>
