<?php

namespace Models;

use ReflectionClass;
use ReflectionProperty;

/**
 * Базовая модель с автоматическим маппингом и приведением типов
 */
abstract class BaseModel
{
    /**
     * Создает экземпляр модели из массива данных БД
     *
     * @param array $data Данные из PDO (обычно все значения - строки)
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        $reflection = new ReflectionClass($instance);

        foreach ($data as $key => $value) {
            // Проверяем, существует ли такое свойство в классе модели
            if (!property_exists($instance, $key)) {
                continue;
            }

            $property = new ReflectionProperty($instance, $key);
            $type = $property->getType();

            // Если тип не указан, записываем как есть
            if (!$type instanceof \ReflectionNamedType) {
                $instance->{$key} = $value;
                continue;
            }

            $typeName = $type->getName();

            // Если значение NULL и тип позволяет (nullable), записываем NULL
            if ($value === null && $type->allowsNull()) {
                $instance->{$key} = null;
                continue;
            }

            // Автоматическое приведение типов (Маппинг)
            $instance->{$key} = match ($typeName) {
                'int'    => (int)$value,
                'float'  => (float)$value,
                'bool'   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'string' => (string)$value,
                default  => $value,
            };
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
