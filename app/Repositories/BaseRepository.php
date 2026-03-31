<?php

namespace Repositories;

use Core\Contract;
use Contracts\DatabaseContract;

/**
 * Абстрактный базовый класс для всех репозиториев системы.
 * Обеспечивает доступ к базе данных через центральный контейнер зависимостей.
 */
abstract class BaseRepository
{
    /**
     * Экземпляр драйвера базы данных, реализующий DatabaseContract.
     * Доступен во всех дочерних репозиториях через $this->db.
     *
     * @var DatabaseContract
     */
    protected DatabaseContract $db;

    /**
     * Конструктор репозитория.
     * Автоматически запрашивает активную реализацию базы данных у DI-контейнера.
     */
    public function __construct()
    {
        /**
         * Contract::make вернет объект MySQLDatabase или PostgresDatabase
         * в зависимости от настроек в твоем .env файле.
         */
        $this->db = Contract::make(DatabaseContract::class);
    }
}
