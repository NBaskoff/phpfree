<?php

namespace Models;

/**
 * Модель данных пользователя
 */
class UserModel extends BaseModel
{
    /** @var int Идентификатор пользователя */
    public int $id;

    /** @var string Имя пользователя */
    public string $name;

    /** @var string Электронная почта */
    public string $email;

    /** @var string|null Дата регистрации */
    public ?string $created_at = null;

    /** @var string|null Дата последнего обновления */
    public ?string $updated_at = null;

    /**
     * Пример метода бизнес-логики: проверка административных прав по домену
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return str_ends_with($this->email, '@admin.com');
    }

    /**
     * Форматирует дату создания для вывода в интерфейсе
     *
     * @return string
     */
    public function getFormattedDate(): string
    {
        return $this->created_at ? date('d.m.Y H:i', strtotime($this->created_at)) : 'Не указана';
    }
}
