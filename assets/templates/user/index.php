<h1><?= $title ?></h1>

<table border="1" cellpadding="10" cellspacing="0">
	<thead>
	<tr>
		<th>ID</th>
		<th>Имя</th>
		<th>Email</th>
		<th>Роли</th>
		<th>Дата регистрации</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user): ?>
		<tr>
			<td><?= $user->id ?></td>
			<td>
				<?= $user->name ?>
				<?php if ($user->hasRole('admin')): ?>
					<span style="color: red;">[ADM]</span>
				<?php endif; ?>
			</td>
			<td><?= $user->email ?></td>
			<td>
				<?php if (!empty($user->roles)): ?>
					<?php
					// Извлекаем только названия (name) из массива ролей
					$roleNames = array_column($user->roles, 'name');
					echo implode(', ', $roleNames);
					?>
				<?php else: ?>
					<i style="color: gray;">нет ролей</i>
				<?php endif; ?>
			</td>
			<td><?= $user->getFormattedDate() ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
