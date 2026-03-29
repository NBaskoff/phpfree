<?php

namespace Models;

/**
 * Абстрактная модель для автоматического маппинга данных
 */
abstract class BaseModel
{
    /**
     * Создает экземпляр модели из массива данных
     *
     * @param array $data Данные из БД
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();

        foreach ($data as $key => $value) {
            // Проверяем, существует ли свойство в дочернем классе
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }

        return $instance;
    }

    /**
     * Преобразует объект модели обратно в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
