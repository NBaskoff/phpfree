<?php
return [
    // Название команды => Класс реализации
    'make:migration'   => \Commands\MakeMigrationCommand::class,
    'migrate'          => \Commands\MigrateCommand::class,
    'migrate:rollback' => \Commands\MigrateRollbackCommand::class,
    'migrate:refresh'  => \Commands\MigrateRefreshCommand::class,
];